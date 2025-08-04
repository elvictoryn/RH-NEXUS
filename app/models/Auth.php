<?php
class Auth {
    
    /**
     * Roles disponibles en el sistema
     */
    const ROLES = [
        'admin' => 'Administrador',
        'rh' => 'Recursos Humanos',
        'gerente' => 'Gerente',
        'jefe_area' => 'Jefe de Área'
    ];

    /**
     * Permisos por módulo y rol según línea de referencia
     */
    const PERMISOS = [
        'usuarios' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['crear', 'leer', 'actualizar'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'departamentos' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['leer'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'sedes' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['leer'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'solicitudes' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['leer', 'procesar'],
            'gerente' => ['leer', 'aprobar'],
            'jefe_area' => ['crear', 'leer', 'actualizar']
        ],
        'evaluaciones' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['crear', 'leer', 'actualizar'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'resultados' => [
            'admin' => ['leer', 'configurar'],
            'rh' => ['leer'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'ia' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar', 'configurar'],
            'rh' => ['leer'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ],
        'configuracion' => [
            'admin' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'rh' => ['leer'],
            'gerente' => ['leer'],
            'jefe_area' => ['leer']
        ]
    ];

    /**
     * Configuración de selección de sede/departamento por rol
     */
    const CONFIGURACION_SELECCION = [
        'admin' => [
            'requiere_sede' => false,
            'requiere_departamento' => false,
            'descripcion' => 'Acceso total al sistema sin restricciones de sede/departamento'
        ],
        'gerente' => [
            'requiere_sede' => true,
            'requiere_departamento' => false,
            'descripcion' => 'Debe seleccionar sede para supervisar toda la operación'
        ],
        'jefe_area' => [
            'requiere_sede' => true,
            'requiere_departamento' => true,
            'descripcion' => 'Debe seleccionar sede y departamento específico'
        ],
        'rh' => [
            'requiere_sede' => true,
            'requiere_departamento' => true,
            'descripcion' => 'Debe seleccionar sede y departamento para gestión local'
        ]
    ];

    /**
     * Vistas permitidas por rol
     */
    const VISTAS_PERMITIDAS = [
        'admin' => [
            '/admin/index.php',
            '/admin/usuarios/',
            '/admin/departamentos/',
            '/admin/solicitudes/',
            '/admin/evaluaciones/',
            '/admin/resultados/',
            '/admin/ia/'
        ],
        'rh' => [
            '/rh/index.php',
            '/rh/usuarios/',
            '/rh/departamentos/',
            '/rh/solicitudes/',
            '/rh/evaluaciones/',
            '/rh/resultados/'
        ],
        'gerente' => [
            '/gerente/index.php',
            '/gerente/usuarios/',
            '/gerente/departamentos/',
            '/gerente/solicitudes/',
            '/gerente/evaluaciones/',
            '/gerente/resultados/'
        ],
        'jefe_area' => [
            '/jefe_area/index.php',
            '/jefe_area/usuarios/',
            '/jefe_area/departamentos/',
            '/jefe_area/solicitudes/',
            '/jefe_area/evaluaciones/',
            '/jefe_area/resultados/'
        ]
    ];

    /**
     * Verificar si el usuario está autenticado
     */
    public static function estaAutenticado() {
        return isset($_SESSION['usuario']) && isset($_SESSION['rol']);
    }

    /**
     * Obtener el rol del usuario actual
     */
    public static function obtenerRol() {
        return $_SESSION['rol'] ?? null;
    }

    /**
     * Obtener el usuario actual
     */
    public static function obtenerUsuario() {
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function tieneRol($rol) {
        return self::obtenerRol() === $rol;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public static function tieneAlgunRol(array $roles) {
        return in_array(self::obtenerRol(), $roles);
    }

    /**
     * Verificar si el usuario tiene permiso para una acción específica en un módulo
     */
    public static function tienePermiso($modulo, $accion) {
        $rol = self::obtenerRol();
        
        if (!$rol) {
            return false;
        }

        if (!isset(self::PERMISOS[$modulo])) {
            return false;
        }

        if (!isset(self::PERMISOS[$modulo][$rol])) {
            return false;
        }

        return in_array($accion, self::PERMISOS[$modulo][$rol]);
    }

    /**
     * Verificar si el usuario puede acceder a una vista específica
     */
    public static function puedeAccederVista($ruta) {
        $rol = self::obtenerRol();
        
        if (!$rol) {
            return false;
        }

        if (!isset(self::VISTAS_PERMITIDAS[$rol])) {
            return false;
        }

        foreach (self::VISTAS_PERMITIDAS[$rol] as $rutaPermitida) {
            if (strpos($ruta, $rutaPermitida) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirigir al login si no está autenticado
     */
    public static function requiereAutenticacion() {
        if (!self::estaAutenticado()) {
            // Incluir configuración de rutas para obtener la URL base
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('public/login.php'));
            exit;
        }
    }

    /**
     * Verificar rol específico y redirigir si no coincide
     */
    public static function requiereRol($rol) {
        self::requiereAutenticacion();
        
        if (!self::tieneRol($rol)) {
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('public/dashboard.php'));
            exit;
        }
    }

    /**
     * Verificar que el usuario tenga alguno de los roles especificados
     */
    public static function requiereAlgunRol(array $roles) {
        self::requiereAutenticacion();
        
        if (!self::tieneAlgunRol($roles)) {
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('public/dashboard.php'));
            exit;
        }
    }

    /**
     * Verificar permiso para módulo y acción específica
     */
    public static function requierePermiso($modulo, $accion) {
        self::requiereAutenticacion();
        
        if (!self::tienePermiso($modulo, $accion)) {
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('public/dashboard.php'));
            exit;
        }
    }

    /**
     * Verificar acceso a vista específica
     */
    public static function requiereAccesoVista($ruta) {
        self::requiereAutenticacion();
        
        if (!self::puedeAccederVista($ruta)) {
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('public/dashboard.php'));
            exit;
        }
    }

    /**
     * Obtener la URL del dashboard según el rol
     */
    public static function obtenerUrlDashboard($rol = null) {
        require_once __DIR__ . '/../config/paths.php';
        $rol = $rol ?? self::obtenerRol();
        
        switch ($rol) {
            case 'admin':
                return base_url('app/views/admin/index.php');
            case 'rh':
                return base_url('app/views/rh/index.php');
            case 'gerente':
                return base_url('app/views/gerente/index.php');
            case 'jefe_area':
                return base_url('app/views/jefe_area/index.php');
            default:
                return base_url('public/login.php');
        }
    }

    /**
     * Redirigir al dashboard correspondiente según el rol
     */
    public static function redirigirDashboard() {
        $url = self::obtenerUrlDashboard();
        header("Location: $url");
        exit;
    }

    /**
     * Cerrar sesión
     */
    public static function cerrarSesion() {
        session_start();
        session_destroy();
        require_once __DIR__ . '/../config/paths.php';
        header('Location: ' . base_url('public/login.php'));
        exit;
    }

    /**
     * Obtener información del usuario actual
     */
    public static function obtenerInfoUsuario() {
        return [
            'usuario' => self::obtenerUsuario(),
            'rol' => self::obtenerRol(),
            'nombre_rol' => self::ROLES[self::obtenerRol()] ?? 'Desconocido',
            'esta_autenticado' => self::estaAutenticado()
        ];
    }

    /**
     * Renderizar elementos del menú según permisos
     */
    public static function renderizarMenuSegunRol() {
        $rol = self::obtenerRol();
        $items = [];

        if (self::tienePermiso('usuarios', 'leer')) {
            $items[] = [
                'url' => 'usuarios/',
                'icono' => 'fas fa-users',
                'texto' => 'Usuarios',
                'permisos' => self::PERMISOS['usuarios'][$rol] ?? []
            ];
        }

        if (self::tienePermiso('departamentos', 'leer')) {
            $items[] = [
                'url' => 'departamentos/',
                'icono' => 'fas fa-sitemap',
                'texto' => 'Departamentos',
                'permisos' => self::PERMISOS['departamentos'][$rol] ?? []
            ];
        }

        if (self::tienePermiso('solicitudes', 'leer')) {
            $items[] = [
                'url' => 'solicitudes/',
                'icono' => 'fas fa-envelope',
                'texto' => 'Solicitudes',
                'permisos' => self::PERMISOS['solicitudes'][$rol] ?? []
            ];
        }

        if (self::tienePermiso('evaluaciones', 'leer')) {
            $items[] = [
                'url' => 'evaluaciones/',
                'icono' => 'fas fa-check-circle',
                'texto' => 'Evaluaciones',
                'permisos' => self::PERMISOS['evaluaciones'][$rol] ?? []
            ];
        }

        if (self::tienePermiso('resultados', 'leer')) {
            $items[] = [
                'url' => 'resultados/',
                'icono' => 'fas fa-chart-line',
                'texto' => 'Resultados',
                'permisos' => self::PERMISOS['resultados'][$rol] ?? []
            ];
        }

        if (self::tienePermiso('ia', 'leer')) {
            $items[] = [
                'url' => 'ia/',
                'icono' => 'fas fa-robot',
                'texto' => 'Módulo IA',
                'permisos' => self::PERMISOS['ia'][$rol] ?? []
            ];
        }

        return $items;
    }

    /**
     * Generar botones de acción según permisos
     */
    public static function generarBotonesAccion($modulo, $id = null) {
        $rol = self::obtenerRol();
        $permisos = self::PERMISOS[$modulo][$rol] ?? [];
        $botones = [];

        if (in_array('leer', $permisos) && $id) {
            $botones[] = "<a href='ver.php?id=$id' class='btn btn-info btn-sm'><i class='fas fa-eye'></i> Ver</a>";
        }

        if (in_array('actualizar', $permisos) && $id) {
            $botones[] = "<a href='editar.php?id=$id' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Editar</a>";
        }

        if (in_array('eliminar', $permisos) && $id) {
            $botones[] = "<a href='eliminar.php?id=$id' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Estás seguro?\");'><i class='fas fa-trash'></i> Eliminar</a>";
        }

        if (in_array('crear', $permisos) && !$id) {
            $botones[] = "<a href='crear.php' class='btn btn-success btn-sm'><i class='fas fa-plus'></i> Crear Nuevo</a>";
        }

        // Botones específicos por acción
        if (in_array('aprobar', $permisos) && $id) {
            $botones[] = "<a href='aprobar.php?id=$id' class='btn btn-success btn-sm'><i class='fas fa-check'></i> Aprobar</a>";
        }

        if (in_array('procesar', $permisos) && $id) {
            $botones[] = "<a href='procesar.php?id=$id' class='btn btn-primary btn-sm'><i class='fas fa-cogs'></i> Procesar</a>";
        }

        if (in_array('configurar', $permisos) && $id) {
            $botones[] = "<a href='configurar.php?id=$id' class='btn btn-secondary btn-sm'><i class='fas fa-cog'></i> Configurar</a>";
        }

        return implode(' ', $botones);
    }

    /**
     * Obtener configuración de selección para el rol actual
     */
    public static function obtenerConfiguracionSeleccion($rol = null) {
        $rol = $rol ?? self::obtenerRol();
        return self::CONFIGURACION_SELECCION[$rol] ?? [
            'requiere_sede' => false,
            'requiere_departamento' => false,
            'descripcion' => 'Configuración no definida'
        ];
    }

    /**
     * Verificar si el rol requiere selección de sede
     */
    public static function requiereSeleccionSede($rol = null) {
        $config = self::obtenerConfiguracionSeleccion($rol);
        return $config['requiere_sede'];
    }

    /**
     * Verificar si el rol requiere selección de departamento
     */
    public static function requiereSeleccionDepartamento($rol = null) {
        $config = self::obtenerConfiguracionSeleccion($rol);
        return $config['requiere_departamento'];
    }

    /**
     * Obtener descripción de la configuración del rol
     */
    public static function obtenerDescripcionConfiguracion($rol = null) {
        $config = self::obtenerConfiguracionSeleccion($rol);
        return $config['descripcion'];
    }

    /**
     * Verificar si el usuario tiene sede y departamento seleccionados según su rol
     */
    public static function verificarSeleccionContexto() {
        $rol = self::obtenerRol();
        $config = self::obtenerConfiguracionSeleccion($rol);

        if ($config['requiere_sede'] && !isset($_SESSION['sede_seleccionada'])) {
            return false;
        }

        if ($config['requiere_departamento'] && !isset($_SESSION['departamento_seleccionado'])) {
            return false;
        }

        return true;
    }

    /**
     * Requerir selección de contexto según el rol
     */
    public static function requerirSeleccionContexto() {
        if (!self::verificarSeleccionContexto()) {
            require_once __DIR__ . '/../config/paths.php';
            header('Location: ' . base_url('app/views/seleccion_contexto.php'));
            exit;
        }
    }

    /**
     * Obtener sede seleccionada del usuario
     */
    public static function obtenerSedeSeleccionada() {
        return $_SESSION['sede_seleccionada'] ?? null;
    }

    /**
     * Obtener departamento seleccionado del usuario
     */
    public static function obtenerDepartamentoSeleccionado() {
        return $_SESSION['departamento_seleccionado'] ?? null;
    }

    /**
     * Establecer sede seleccionada
     */
    public static function establecerSedeSeleccionada($sede_id, $sede_nombre) {
        $_SESSION['sede_seleccionada'] = $sede_id;
        $_SESSION['sede_nombre'] = $sede_nombre;
    }

    /**
     * Establecer departamento seleccionado
     */
    public static function establecerDepartamentoSeleccionado($departamento_id, $departamento_nombre) {
        $_SESSION['departamento_seleccionado'] = $departamento_id;
        $_SESSION['departamento_nombre'] = $departamento_nombre;
    }

    /**
     * Limpiar contexto seleccionado
     */
    public static function limpiarContextoSeleccionado() {
        unset($_SESSION['sede_seleccionada']);
        unset($_SESSION['sede_nombre']);
        unset($_SESSION['departamento_seleccionado']);
        unset($_SESSION['departamento_nombre']);
    }
}