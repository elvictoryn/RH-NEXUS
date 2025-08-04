<?php
/**
 * Funciones auxiliares para el sistema de autenticación
 * Incluir este archivo en las vistas que necesiten control de acceso
 */

// Asegurar que las sesiones estén iniciadas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir la clase Auth
safe_require_once(model_path('Auth'));

/**
 * Función simplificada para verificar autenticación
 */
function verificarAuth() {
    Auth::requiereAutenticacion();
}

/**
 * Función simplificada para verificar rol específico
 */
function verificarRol($rol) {
    Auth::requiereRol($rol);
}

/**
 * Función simplificada para verificar múltiples roles
 */
function verificarRoles(array $roles) {
    Auth::requiereAlgunRol($roles);
}

/**
 * Función simplificada para verificar permisos
 */
function verificarPermiso($modulo, $accion) {
    Auth::requierePermiso($modulo, $accion);
}

/**
 * Función para mostrar/ocultar elementos según permisos
 */
function mostrarSiTienePermiso($modulo, $accion, $contenido) {
    if (Auth::tienePermiso($modulo, $accion)) {
        echo $contenido;
    }
}

/**
 * Función para mostrar/ocultar elementos según rol
 */
function mostrarSiTieneRol($rol, $contenido) {
    if (Auth::tieneRol($rol)) {
        echo $contenido;
    }
}

/**
 * Función para mostrar/ocultar elementos según múltiples roles
 */
function mostrarSiTieneAlgunRol(array $roles, $contenido) {
    if (Auth::tieneAlgunRol($roles)) {
        echo $contenido;
    }
}

/**
 * Función para obtener información del usuario actual
 */
function obtenerUsuarioActual() {
    return Auth::obtenerInfoUsuario();
}

/**
 * Función para generar menú según rol
 */
function generarMenuRol() {
    return Auth::renderizarMenuSegunRol();
}

/**
 * Función para generar botones de acción según permisos
 */
function generarBotones($modulo, $id = null) {
    return Auth::generarBotonesAccion($modulo, $id);
}

/**
 * Función para redirigir al dashboard correspondiente
 */
function irDashboard() {
    Auth::redirigirDashboard();
}

/**
 * Función para cerrar sesión
 */
function cerrarSesion() {
    Auth::cerrarSesion();
}

/**
 * Funciones para validaciones específicas por vista
 */

// Para vistas de administrador únicamente
function soloAdmin() {
    verificarRol('admin');
}

// Para vistas de admin y RH
function adminORH() {
    verificarRoles(['admin', 'rh']);
}

// Para vistas de admin, RH y gerente
function adminRhGerente() {
    verificarRoles(['admin', 'rh', 'gerente']);
}

// Para todas las vistas (solo autenticación)
function usuarioAutenticado() {
    verificarAuth();
}

/**
 * Funciones para manejo de contexto según rol
 */

// Verificar y requerir selección de contexto según rol
function verificarContextoRol() {
    Auth::requerirSeleccionContexto();
}

// Obtener configuración de selección del rol actual
function obtenerConfiguracionRol($rol = null) {
    return Auth::obtenerConfiguracionSeleccion($rol);
}

// Verificar si requiere selección de sede
function requiereSeleccionSede($rol = null) {
    return Auth::requiereSeleccionSede($rol);
}

// Verificar si requiere selección de departamento
function requiereSeleccionDepartamento($rol = null) {
    return Auth::requiereSeleccionDepartamento($rol);
}

// Obtener sede seleccionada
function obtenerSedeSeleccionada() {
    return Auth::obtenerSedeSeleccionada();
}

// Obtener departamento seleccionado
function obtenerDepartamentoSeleccionado() {
    return Auth::obtenerDepartamentoSeleccionado();
}

// Establecer sede seleccionada
function establecerSedeSeleccionada($sede_id, $sede_nombre) {
    Auth::establecerSedeSeleccionada($sede_id, $sede_nombre);
}

// Establecer departamento seleccionado
function establecerDepartamentoSeleccionado($departamento_id, $departamento_nombre) {
    Auth::establecerDepartamentoSeleccionado($departamento_id, $departamento_nombre);
}

// Limpiar contexto seleccionado
function limpiarContextoSeleccionado() {
    Auth::limpiarContextoSeleccionado();
}

// Obtener descripción de configuración del rol
function obtenerDescripcionConfiguracion($rol = null) {
    return Auth::obtenerDescripcionConfiguracion($rol);
}

/**
 * Función para obtener el rol traducido
 */
function obtenerNombreRol($rol = null) {
    $rol = $rol ?? Auth::obtenerRol();
    return Auth::ROLES[$rol] ?? 'Desconocido';
}

/**
 * Función para verificar si es administrador
 */
function esAdmin() {
    return Auth::tieneRol('admin');
}

/**
 * Función para verificar si es RH
 */
function esRH() {
    return Auth::tieneRol('rh');
}

/**
 * Función para verificar si es gerente
 */
function esGerente() {
    return Auth::tieneRol('gerente');
}

/**
 * Función para verificar si es jefe de área
 */
function esJefeArea() {
    return Auth::tieneRol('jefe_area');
}

/**
 * Función para generar enlaces de navegación
 */
function generarEnlaceNavegacion($modulo, $accion, $texto, $clase = 'btn btn-primary') {
    if (Auth::tienePermiso($modulo, $accion)) {
        return "<a href='#' class='$clase'>$texto</a>";
    }
    return "<span class='btn btn-secondary disabled'>$texto</span>";
}

/**
 * Función para mostrar alertas según permisos
 */
function mostrarAlertaPermiso($mensaje = "No tienes permisos para realizar esta acción") {
    echo "<div class='alert alert-warning'>$mensaje</div>";
}

/**
 * Función para incluir header con validación de autenticación
 */
function incluirHeaderAuth($titulo = "Sistema RH") {
    verificarAuth();
    $usuario = obtenerUsuarioActual();
    safe_include_once(shared_header_path());
}

/**
 * Función para renderizar la barra lateral con opciones según rol
 */
function renderizarSidebar() {
    $items = generarMenuRol();
    $usuario = obtenerUsuarioActual();
    
    echo "<div class='sidebar bg-dark text-white p-3'>";
    echo "<h4 class='fw-bold'>Nexus RH</h4>";
    echo "<hr>";
    echo "<ul class='nav flex-column'>";
    
    foreach ($items as $item) {
        echo "<li class='nav-item mb-2'>";
        echo "<a class='nav-link text-white' href='{$item['url']}'>";
        echo "<i class='{$item['icono']} me-2'></i>{$item['texto']}";
        echo "</a>";
        echo "</li>";
    }
    
    echo "</ul>";
    echo "<hr>";
    echo "<div class='text-white-50 small'>Usuario: {$usuario['usuario']}</div>";
    echo "<div class='text-white-50 small'>Rol: {$usuario['nombre_rol']}</div>";
    echo "<a href='../../../public/logout.php' class='btn btn-sm btn-outline-light mt-2'>Cerrar sesión</a>";
    echo "</div>";
}