# Guía de Despliegue en InfinityFree - Sistema RH Nexus

## ✅ COMPATIBILIDAD CONFIRMADA

Tu proyecto **SÍ ES COMPATIBLE** con InfinityFree. Aquí está la guía completa de despliegue:

## 📋 PREPARACIÓN PREVIA

### 1. Crear cuenta en InfinityFree
- Ve a [infinityfree.net](https://infinityfree.net)
- Regístrate y crea un nuevo sitio web
- Anota las credenciales de la base de datos

### 2. Preparar archivos del proyecto
- Comprimir toda la carpeta `RH-NEXUS` en un archivo ZIP
- Renombrar la carpeta a `sistema_rh` (sin espacios ni caracteres especiales)

## 🚀 PROCESO DE DESPLIEGUE

### Paso 1: Subir archivos
1. Accede al panel de control de InfinityFree
2. Ve a "File Manager"
3. Navega a la carpeta `htdocs`
4. Sube el archivo ZIP del proyecto
5. Extrae el contenido en `htdocs/sistema_rh/`

### Paso 2: Configurar base de datos
1. En el panel de InfinityFree, ve a "MySQL Databases"
2. Crea una nueva base de datos
3. Anota las credenciales:
   - Host: `sqlXXX.epizy.com`
   - Usuario: `epiz_XXXXXX`
   - Contraseña: `tu_password`
   - Base de datos: `epiz_XXXXXX_sistema_rh`

### Paso 3: Actualizar configuración
1. Edita `config/conexion.php`
2. Reemplaza los valores XXX con tus credenciales reales:
   ```php
   $host = 'sqlXXX.epizy.com';  // Tu host real
   $db = 'epiz_XXXXXX_sistema_rh';  // Tu BD real
   $user = 'epiz_XXXXXX';  // Tu usuario real
   $pass = 'tu_password_real';  // Tu contraseña real
   ```

### Paso 4: Configurar .htaccess
1. Renombra `.htaccess_infinityfree` a `.htaccess`
2. O copia el contenido de `.htaccess_infinityfree` a tu `.htaccess` actual

### Paso 5: Importar base de datos
1. Ve a "phpMyAdmin" en el panel de InfinityFree
2. Selecciona tu base de datos
3. Importa el archivo SQL de tu base de datos local
4. Verifica que todas las tablas se crearon correctamente

## 🔧 CONFIGURACIONES ADICIONALES

### Estructura de directorios en InfinityFree:
```
htdocs/
└── sistema_rh/
    ├── app/
    ├── config/
    ├── public/
    │   ├── .htaccess
    │   ├── index.php
    │   └── css/
    └── python_api/
```

### URL de acceso:
- **Local:** `http://localhost/sistema_rh/public/`
- **InfinityFree:** `http://tudominio.epizy.com/sistema_rh/public/`

## ⚠️ LIMITACIONES DE INFINITYFREE

### Recursos limitados:
- **CPU:** 100% por 60 segundos, luego throttling
- **Memoria:** 128MB máximo
- **Espacio:** 5GB máximo
- **Ancho de banda:** 5GB/mes
- **Tiempo de ejecución:** 30 segundos máximo

### Recomendaciones:
1. **Optimizar imágenes:** Comprimir todas las imágenes en `public/img/`
2. **Minificar CSS/JS:** Reducir tamaño de archivos estáticos
3. **Límite de usuarios:** Máximo 50-100 usuarios concurrentes
4. **Backup regular:** Exportar BD periódicamente

## 🐛 SOLUCIÓN DE PROBLEMAS COMUNES

### Error 500 - Internal Server Error
- Verifica permisos de archivos (644 para archivos, 755 para carpetas)
- Revisa el archivo `.htaccess`
- Verifica la configuración de la base de datos

### Error de conexión a base de datos
- Confirma que las credenciales son correctas
- Verifica que la base de datos existe
- Revisa que el host sea correcto

### Páginas en blanco
- Activa el display_errors en PHP
- Revisa los logs de error en el panel de InfinityFree
- Verifica que todos los archivos se subieron correctamente

## 📊 MONITOREO Y MANTENIMIENTO

### Verificaciones regulares:
1. **Espacio en disco:** Mantener bajo 4GB
2. **Uso de CPU:** Monitorear picos de uso
3. **Base de datos:** Limpiar registros antiguos
4. **Logs:** Revisar errores periódicamente

### Backup recomendado:
- Exportar base de datos semanalmente
- Descargar archivos importantes mensualmente
- Documentar cambios en configuración

## 🎯 RESULTADO FINAL

Una vez completado el despliegue, tu aplicación estará disponible en:
- **URL principal:** `http://tudominio.epizy.com/sistema_rh/public/`
- **Login:** `http://tudominio.epizy.com/sistema_rh/public/login.php`
- **Admin:** `http://tudominio.epizy.com/sistema_rh/public/admin.php`

## 📞 SOPORTE

Si encuentras problemas:
1. Revisa los logs de error en el panel de InfinityFree
2. Consulta la documentación de InfinityFree
3. Verifica que todos los pasos se completaron correctamente

¡Tu proyecto está listo para funcionar en InfinityFree! 🚀
