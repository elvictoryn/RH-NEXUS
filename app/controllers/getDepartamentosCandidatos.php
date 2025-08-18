<?php
/**
 * Controlador específico para obtener departamentos en el módulo de candidatos
 */

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir sistema de autenticación
safe_require_once(includes_path('auth_helpers.php'));

// Verificar autenticación
usuarioAutenticado();

// Incluir modelo de departamento
safe_require_once(model_path('departamento'));

// Configurar headers para JSON
header('Content-Type: application/json');

// Verificar que se envió el parámetro sede_id
if (!isset($_GET['sede_id']) || empty($_GET['sede_id'])) {
    echo json_encode(['error' => 'Sede ID requerido']);
    exit;
}

$sede_id = (int)$_GET['sede_id'];

try {
    $departamento_model = new Departamento();
    $departamentos = $departamento_model->obtenerTodosConSede();
    
    // Filtrar departamentos por sede
    $departamentos_filtrados = array_filter($departamentos, function($dept) use ($sede_id) {
        return $dept['sede_id'] == $sede_id;
    });
    
    // Formatear respuesta
    $response = [];
    foreach ($departamentos_filtrados as $dept) {
        $response[] = [
            'id' => $dept['id'],
            'nombre' => $dept['nombre'],
            'descripcion' => $dept['descripcion'],
            'sede_nombre' => $dept['nombre_sede']
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener departamentos: ' . $e->getMessage()]);
}
?> 