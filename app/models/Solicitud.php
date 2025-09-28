<?php
// app/models/Solicitud.php
if (!defined('BASE_PATH')) define('BASE_PATH', '/sistema_rh');
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';

class Solicitud {
  private PDO $db;
  public function __construct(){ $this->db = Conexion::getConexion(); }

  /* ===== Utilidades ===== */
  public static function uid(): int {
    // ajusta el nombre del índice de sesión si lo usas diferente
    return (int)($_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0);
  }
  public static function rol(): string {
    return strtolower((string)($_SESSION['rol'] ?? ''));
  }

  public function getGerenteDeSede(?int $sedeId): ?int {
    if (!$sedeId) return null;
    $st = $this->db->prepare("SELECT gerente_id FROM sedes WHERE id=:s LIMIT 1");
    $st->execute([':s'=>$sedeId]);
    $g = $st->fetchColumn();
    return $g ? (int)$g : null;
  }

  private function generarFolio(): string {
    // Formato: SOL-YYYYMM-#### (relleno 4)
    $pref = 'SOL-'.date('Ym').'-';
    // buscamos máximo consecutivo del mes
    $st = $this->db->prepare("SELECT folio FROM solicitudes WHERE folio LIKE :pfx ORDER BY id DESC LIMIT 1");
    $st->execute([':pfx'=>$pref.'%']);
    $last = $st->fetchColumn();
    $n = 0;
    if ($last && preg_match('/-(\d{4})$/', $last, $m)) $n = (int)$m[1];
    // Intento de 5 folios por si colisiona (UNIQUE)
    for ($i=0;$i<5;$i++){
      $cand = $pref . str_pad((string)($n+1+$i), 4, '0', STR_PAD_LEFT);
      $ok = $this->db->prepare("SELECT 1 FROM solicitudes WHERE folio=:f LIMIT 1");
      $ok->execute([':f'=>$cand]);
      if (!$ok->fetchColumn()) return $cand;
    }
    // fallback ultra-único
    return $pref . substr(strtoupper(bin2hex(random_bytes(3))),0,6);
  }

  /* ===== Crear =====
     Respeta tu esquema: ver columnas folio/sede_id/departamento_id/gerente_id/... (:contentReference[oaicite:1]{index=1})
  */
  public function crear(array $d): int {
    $this->db->beginTransaction();
    try {
      $folio = $this->generarFolio();

      // reglas por rol
      $rol = self::rol();
      $uid = self::uid();

      // estado / RH auto-asignado si lo crea RH
      $estado = 'pendiente';
      $rh_asignado_id = null;
      if ($rol === 'rh') {
        $estado = 'aprobada';             // RH crea -> se considera aprobada
        $rh_asignado_id = $uid;           // y se autoasigna
      }

      // gerente_id para la cadena de autorización
      $gerente_id = null;
      if ($rol === 'gerente') {
        $gerente_id = $uid;
      } else {
        $gerente_id = $this->getGerenteDeSede((int)$d['sede_id']);
      }

      $sql = "INSERT INTO solicitudes
        (folio, sede_id, departamento_id, gerente_id, creado_por, puesto, titulo, vacantes, motivo, reemplazo_de,
         fecha_ingreso_deseada, justificacion, tipo_contrato, modalidad, horario, salario_min, salario_max,
         escolaridad_min, carrera_estudiada, experiencia_anios, area_experiencia, ingles_req, ingles_nivel_min,
         competencias_json, responsabilidades, etapas_json, prioridad, estado, rh_asignado_id, es_borrador, creado_en, actualizado_en)
        VALUES
        (:folio,:sede_id,:departamento_id,:gerente_id,:creado_por,:puesto,:titulo,:vacantes,:motivo,:reemplazo_de,
         :fecha_ingreso_deseada,:justificacion,:tipo_contrato,:modalidad,:horario,:salario_min,:salario_max,
         :escolaridad_min,:carrera_estudiada,:experiencia_anios,:area_experiencia,:ingles_req,:ingles_nivel_min,
         :competencias_json,:responsabilidades,:etapas_json,:prioridad,:estado,:rh_asignado_id,:es_borrador,NOW(),NOW())";

      $st = $this->db->prepare($sql);
      $st->execute([
        ':folio'=>$folio,
        ':sede_id'=> (int)$d['sede_id'],
        ':departamento_id'=> (int)$d['departamento_id'],
        ':gerente_id'=> $gerente_id ?: 0,
        ':creado_por'=> $uid,
        ':puesto'=> trim($d['puesto']),
        ':titulo'=> trim($d['titulo']),
        ':vacantes'=> (int)$d['vacantes'],
        ':motivo'=> $d['motivo'],
        ':reemplazo_de'=> $d['reemplazo_de'] ?: null,
        ':fecha_ingreso_deseada'=> $d['fecha_ingreso_deseada'] ?: null,
        ':justificacion'=> trim($d['justificacion']),
        ':tipo_contrato'=> $d['tipo_contrato'],
        ':modalidad'=> $d['modalidad'],
        ':horario'=> trim($d['horario']),
        ':salario_min'=> (int)$d['salario_min'],
        ':salario_max'=> (int)$d['salario_max'],
        ':escolaridad_min'=> $d['escolaridad_min'],
        ':carrera_estudiada'=> $d['carrera_estudiada'] ?: null,
        ':experiencia_anios'=> (int)$d['experiencia_anios'],
        ':area_experiencia'=> $d['area_experiencia'] ?: null,
        ':ingles_req'=> $d['ingles_req'],
        ':ingles_nivel_min'=> $d['ingles_nivel_min'] !== '' ? (int)$d['ingles_nivel_min'] : null,
        ':competencias_json'=> !empty($d['competencias']) ? json_encode($d['competencias'], JSON_UNESCAPED_UNICODE) : null,
        ':responsabilidades'=> $d['responsabilidades'] ?: null,
        ':etapas_json'=> !empty($d['etapas']) ? json_encode($d['etapas'], JSON_UNESCAPED_UNICODE) : null,
        ':prioridad'=> $d['prioridad'],
        ':estado'=> $estado,                            // ← lógica por rol
        ':rh_asignado_id'=> $rh_asignado_id,           // ← auto-asigna si RH
        ':es_borrador'=> !empty($d['es_borrador']) ? 1 : 0
      ]);

      $id = (int)$this->db->lastInsertId();
      $this->db->commit();
      return $id;
    } catch(Throwable $e){
      if ($this->db->inTransaction()) $this->db->rollBack();
      throw $e;
    }
  }
}
