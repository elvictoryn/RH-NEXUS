<?php
/**
 * Controlador de prueba para verificar funcionalidades del módulo de solicitudes para RH
 */

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

// Configurar headers para JSON
header('Content-Type: application/json');

try {
    $solicitud_model = new Solicitud();
    
    // Obtener todas las solicitudes (RH debe poder ver todas)
    $todas = $solicitud_model->obtenerTodas();
    
    // Obtener solicitudes por estado específico
    $aceptadas_gerencia = $solicitud_model->obtenerPorEstado('aceptada gerencia');
    $en_proceso_rh = $solicitud_model->obtenerPorEstado('en proceso rh');
    $solicita_cambios = $solicitud_model->obtenerPorEstado('solicita cambios');
    $cerradas = $solicitud_model->obtenerPorEstado('cerrada');
    
    // Verificar que RH puede ver solicitudes de diferentes sedes/departamentos
    $sedes_unicas = [];
    $departamentos_unicos = [];
    
    foreach ($todas as $solicitud) {
        $sedes_unicas[$solicitud['sede_id']] = $solicitud['sede_nombre'];
        $departamentos_unicos[$solicitud['departamento_id']] = $solicitud['departamento_nombre'];
    }
    
    $response = [
        'success' => true,
        'total_solicitudes' => count($todas),
        'estadisticas_por_estado' => [
            'aceptada gerencia' => count($aceptadas_gerencia),
            'en proceso rh' => count($en_proceso_rh),
            'solicita cambios' => count($solicita_cambios),
            'cerrada' => count($cerradas)
        ],
        'cobertura_sistema' => [
            'sedes_unicas' => count($sedes_unicas),
            'departamentos_unicos' => count($departamentos_unicos),
            'sedes' => $sedes_unicas,
            'departamentos' => $departamentos_unicos
        ],
        'ejemplos_solicitudes' => [
            'aceptada_gerencia' => !empty($aceptadas_gerencia) ? [
                'id' => $aceptadas_gerencia[0]['id'],
                'codigo' => $aceptadas_gerencia[0]['codigo'],
                'estado' => $aceptadas_gerencia[0]['estado'],
                'sede' => $aceptadas_gerencia[0]['sede_nombre'],
                'departamento' => $aceptadas_gerencia[0]['departamento_nombre']
            ] : null,
            'en_proceso_rh' => !empty($en_proceso_rh) ? [
                'id' => $en_proceso_rh[0]['id'],
                'codigo' => $en_proceso_rh[0]['codigo'],
                'estado' => $en_proceso_rh[0]['estado'],
                'sede' => $en_proceso_rh[0]['sede_nombre'],
                'departamento' => $en_proceso_rh[0]['departamento_nombre']
            ] : null,
            'solicita_cambios' => !empty($solicita_cambios) ? [
                'id' => $solicita_cambios[0]['id'],
                'codigo' => $solicita_cambios[0]['codigo'],
                'estado' => $solicita_cambios[0]['estado'],
                'cambios_solicitados' => $solicita_cambios[0]['cambios_solicitados'] ?? 'No disponible',
                'sede' => $solicita_cambios[0]['sede_nombre'],
                'departamento' => $solicita_cambios[0]['departamento_nombre']
            ] : null,
            'cerrada' => !empty($cerradas) ? [
                'id' => $cerradas[0]['id'],
                'codigo' => $cerradas[0]['codigo'],
                'estado' => $cerradas[0]['estado'],
                'motivo_cierre' => $cerradas[0]['motivo_cierre'] ?? 'No disponible',
                'sede' => $cerradas[0]['sede_nombre'],
                'departamento' => $cerradas[0]['departamento_nombre']
            ] : null
        ],
        'acciones_disponibles_rh' => [
            'ver_todas_solicitudes' => true,
            'solicitar_cambios' => count($en_proceso_rh) > 0,
            'cerrar_solicitud' => count($en_proceso_rh) > 0,
            'reanudar_proceso' => count($cerradas) > 0,
            'iniciar_proceso_rh' => count($aceptadas_gerencia) > 0
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
