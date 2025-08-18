<?php
/**
 * Archivo de prueba para verificar respuesta JSON
 */

// Configurar headers para JSON
header('Content-Type: application/json');

// Simular respuesta de departamentos
$departamentos = [
    [
        'id' => 1,
        'nombre' => 'Recursos Humanos',
        'descripcion' => 'Departamento de RRHH',
        'sede_nombre' => 'Sede Central'
    ],
    [
        'id' => 2,
        'nombre' => 'Tecnología',
        'descripcion' => 'Departamento de IT',
        'sede_nombre' => 'Sede Central'
    ]
];

echo json_encode($departamentos);
?> 