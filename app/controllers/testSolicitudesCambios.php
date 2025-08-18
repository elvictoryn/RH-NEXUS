<?php
/**
 * Controlador de prueba para verificar que el campo cambios_solicitados esté disponible
 * en la vista v_solicitudes_completa
 */

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

// Configurar headers para JSON
header('Content-Type: application/json');

try {
    $solicitud_model = new Solicitud();
    
    // Obtener todas las solicitudes para ver qué campos están disponibles
    $todas = $solicitud_model->obtenerTodas();
    
    // Obtener una solicitud específica por ID (si existe)
    $primera = !empty($todas) ? $todas[0] : null;
    
    // Verificar campos disponibles
    $campos_disponibles = [];
    if ($primera) {
        $campos_disponibles = array_keys($primera);
    }
    
    // Buscar solicitudes con estado 'solicita cambios'
    $solicita_cambios = $solicitud_model->obtenerPorEstado('solicita cambios');
    
    $response = [
        'success' => true,
        'total_solicitudes' => count($todas),
        'campos_disponibles' => $campos_disponibles,
        'tiene_cambios_solicitados' => in_array('cambios_solicitados', $campos_disponibles),
        'solicitudes_solicita_cambios' => count($solicita_cambios),
        'ejemplo_solicitud' => $primera ? [
            'id' => $primera['id'],
            'codigo' => $primera['codigo'],
            'estado' => $primera['estado'],
            'cambios_solicitados' => $primera['cambios_solicitados'] ?? 'CAMPO NO DISPONIBLE'
        ] : null,
        'solicitudes_con_cambios' => array_map(function($s) {
            return [
                'id' => $s['id'],
                'codigo' => $s['codigo'],
                'estado' => $s['estado'],
                'cambios_solicitados' => $s['cambios_solicitados'] ?? 'CAMPO NO DISPONIBLE'
            ];
        }, $solicita_cambios)
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
