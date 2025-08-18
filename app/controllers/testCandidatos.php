<?php
/**
 * Archivo de prueba para verificar funcionalidades del módulo de candidatos
 */

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir modelo de candidato
safe_require_once(model_path('Candidato'));

// Configurar headers para JSON
header('Content-Type: application/json');

try {
    $candidato_model = new Candidato();
    
    // Obtener estadísticas
    $estadisticas = $candidato_model->obtenerEstadisticas();
    
    // Obtener todos los candidatos (incluyendo inactivos para prueba)
    $todos = $candidato_model->obtenerTodos();
    
    $response = [
        'success' => true,
        'estadisticas' => $estadisticas,
        'total_candidatos' => count($todos),
        'candidatos_por_estado' => [
            'activo' => 0,
            'inactivo' => 0,
            'contratado' => 0,
            'rechazado' => 0
        ]
    ];
    
    // Contar candidatos por estado
    foreach ($todos as $candidato) {
        $estado = $candidato['estado'] ?? 'desconocido';
        if (isset($response['candidatos_por_estado'][$estado])) {
            $response['candidatos_por_estado'][$estado]++;
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?> 