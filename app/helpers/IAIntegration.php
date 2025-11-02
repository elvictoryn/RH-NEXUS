<?php
// app/helpers/IAIntegration.php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/services/IAClient.php';

/**
 * Normaliza a MAYÚSCULAS y quita acentos (si intl está activo; si no, hace un fallback).
 */
function ia_norm_upper_no_accents(?string $s): string {
    $s = trim((string)$s);
    $s = mb_strtoupper($s, 'UTF-8');
    if (class_exists('Normalizer')) {
        $s = Normalizer::normalize($s, Normalizer::FORM_D);
        $s = preg_replace('/\p{Mn}+/u', '', $s); // elimina marcas diacríticas
    } else {
        // Fallback simple sin intl
        $repl = [
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N',
            'À'=>'A','È'=>'E','Ì'=>'I','Ò'=>'O','Ù'=>'U',
            'Ä'=>'A','Ë'=>'E','Ï'=>'I','Ö'=>'O','Ü'=>'U',
        ];
        $s = strtr($s, $repl);
    }
    return $s;
}

/** Catálogo que espera la IA */
function ia_normalize_education(?string $val): string {
    $v = ia_norm_upper_no_accents($val);
    if ($v === 'MAESTRIA') $v = 'MAESTRÍA';
    return $v;
}
function ia_normalize_strategy(?string $val): string {
    $v = ia_norm_upper_no_accents($val);
    if ($v === 'PORTAL DE EMPLEO' || $v === 'PORTALES') $v = 'PORTALES DE EMPLEO';
    if ($v === 'RECOMENDADA') $v = 'RECOMENDADO';
    return $v;
}

/**
 * Obtiene datos mínimos del candidato para IA.
 * (OJO: columnas con espacios/acentos => usar backticks siempre)
 */
function ia_get_candidate_row(PDO $db, int $candidato_id): ?array {
    $sql = "
        SELECT
            `id`,
            `solicitud_id`,
            `EducationLevel`,
            `ExperienceYears`,
            `RecruitmentStrategy`,
            `PersonalityScore`,
            `SkillScore`,
            `InterviewScore`
        FROM `postulantes_por_vacante`
        WHERE `id` = :id
        LIMIT 1
    ";
    $st = $db->prepare($sql);
    $st->execute([':id'=>$candidato_id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * /predict para un candidato y guarda:
 *  - `Puntaje de evaluación`
 *  - `Viabilidad`
 */
function ia_predict_for_candidate(int $candidato_id): array {
    $db = Conexion::getConexion();
    $row = ia_get_candidate_row($db, $candidato_id);
    if (!$row) {
        throw new RuntimeException("Candidato no encontrado (ID $candidato_id).");
    }

    $need = ['EducationLevel','ExperienceYears','RecruitmentStrategy','PersonalityScore','SkillScore','InterviewScore'];
    foreach ($need as $k) {
        if (!isset($row[$k]) || $row[$k]==='' || $row[$k]===null) {
            throw new RuntimeException("Falta el campo requerido para IA: $k.");
        }
    }

    $payload = [
        'EducationLevel'      => ia_normalize_education($row['EducationLevel']),
        'ExperienceYears'     => (int)$row['ExperienceYears'],
        'RecruitmentStrategy' => ia_normalize_strategy($row['RecruitmentStrategy']),
        'PersonalityScore'    => (int)$row['PersonalityScore'],
        'SkillScore'          => (int)$row['SkillScore'],
        'InterviewScore'      => (int)$row['InterviewScore'],
    ];

    $ia = new IAClient();
    $resp = $ia->predict($payload);
    // { ok:true, evaluation_score:int, viability:'ALTA|MEDIA|BAJA' }
    $evaluation = (int)($resp['evaluation_score'] ?? 0);
    $viability  = (string)($resp['viability'] ?? '');

    $up = $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `Puntaje de evaluación` = :eval,
               `Viabilidad`            = :via
         WHERE `id` = :id
    ");
    $up->execute([':eval'=>$evaluation, ':via'=>$viability, ':id'=>$candidato_id]);

    return ['evaluation_score'=>$evaluation, 'viability'=>$viability];
}

/**
 * /rank para TODOS los candidatos evaluados de una solicitud.
 * Corrige el 400 "'str' object has no attribute 'get'" asegurando que se envíe
 * un ARRAY de objetos al cliente (que internamente lo empaqueta como {"items":[...]})
 */
function ia_rank_for_solicitud(int $solicitud_id): int {
    $db = Conexion::getConexion();

    $st = $db->prepare("
        SELECT `id`, `Puntaje de evaluación`
        FROM `postulantes_por_vacante`
        WHERE `solicitud_id` = :sid
          AND `Puntaje de evaluación` IS NOT NULL
    ");
    $st->execute([':sid'=>$solicitud_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!$rows) return 0;

    // Construir SIEMPRE una lista (array indexado) de objetos {id, evaluation_score}
    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'id' => (int)$r['id'],
            'evaluation_score' => (int)$r['Puntaje de evaluación'],
        ];
    }

    // Llamar IA: IAClient::rank YA envuelve como {"items": [...]}.
    $ia = new IAClient();
    $resp = $ia->rank($items);

    $itemsSorted = $resp['items'] ?? [];
    if (!is_array($itemsSorted)) return 0;

    $up = $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `PosiciónRanking` = :rk
         WHERE `id` = :id
           AND `solicitud_id` = :sid
    ");

    $n = 0;
    foreach ($itemsSorted as $it) {
        // validación defensiva por si algo raro vuelve del servicio
        $id = isset($it['id']) ? (int)$it['id'] : 0;
        $rk = isset($it['rank']) ? (int)$it['rank'] : 0;
        if ($id > 0 && $rk > 0) {
            $up->execute([':rk'=>$rk, ':id'=>$id, ':sid'=>$solicitud_id]);
            $n++;
        }
    }
    return $n;
}
