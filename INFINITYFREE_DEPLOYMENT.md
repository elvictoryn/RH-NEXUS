# 🚀 Despliegue en InfinityFree - Sistema RH Nexus

## ✅ **CREDENCIALES CONFIGURADAS**

Tu proyecto ya está configurado con las credenciales de InfinityFree:

- **Host:** `sql306.infinityfree.com`
- **Usuario:** `if0_40170523`
- **Contraseña:** `a171LhcQWeVh`
- **Base de datos:** `if0_40170523_sistema_rh`
- **Puerto:** `3306`

## 📁 **PASOS DE DESPLIEGUE**

### 1. **Preparar archivos**
```bash
# Comprimir el proyecto
cd RH-NEXUS
zip -r sistema_rh.zip . -x "*.git*" "*.DS_Store*" "*.log*"
```

### 2. **Subir a InfinityFree**
1. Accede al panel de control de InfinityFree
2. Ve a **"File Manager"**
3. Navega a la carpeta `htdocs`
4. Sube el archivo `sistema_rh.zip`
5. Extrae el contenido en `htdocs/sistema_rh/`

### 3. **Configurar .htaccess**
1. Renombra `.htaccess_infinityfree` a `.htaccess`
2. O copia el contenido de `.htaccess_infinityfree` a tu `.htaccess` actual

### 4. **Importar base de datos**
1. Ve a **"phpMyAdmin"** en el panel de InfinityFree
2. Selecciona tu base de datos `if0_40170523_sistema_rh`
3. Importa tu archivo SQL de la base de datos local
4. Verifica que todas las tablas se crearon correctamente

## 🔧 **CONFIGURACIÓN AUTOMÁTICA**

El proyecto detecta automáticamente si está en InfinityFree y usa las credenciales correctas:

```php
// En config/environment.php
function isInfinityFree(): bool {
    return isset($_SERVER['HTTP_HOST']) && 
           (strpos($_SERVER['HTTP_HOST'], 'epizy.com') !== false || 
            strpos($_SERVER['HTTP_HOST'], 'infinityfree.net') !== false);
}
```

## 🌐 **URLs DE ACCESO**

Una vez desplegado, tu aplicación estará disponible en:

- **URL principal:** `http://tudominio.epizy.com/sistema_rh/public/`
- **Login:** `http://tudominio.epizy.com/sistema_rh/public/login.php`
- **Admin:** `http://tudominio.epizy.com/sistema_rh/public/admin.php`

## ⚠️ **LIMITACIONES DE INFINITYFREE**

### Recursos limitados:
- **CPU:** 100% por 60 segundos, luego throttling
- **Memoria:** 128MB máximo
- **Espacio:** 5GB máximo
- **Ancho de banda:** 5GB/mes
- **Tiempo de ejecución:** 30 segundos máximo

### Optimizaciones implementadas:
- ✅ Conexiones no persistentes
- ✅ Queries buffered para servidores compartidos
- ✅ Timeout de 30 segundos
- ✅ Error handling robusto
- ✅ UTF-8 configurado correctamente

## 🐛 **SOLUCIÓN DE PROBLEMAS**

### Error 500 - Internal Server Error
```bash
# Verificar permisos
chmod 644 archivos.php
chmod 755 carpetas/
```

### Error de conexión a base de datos
- Verifica que las credenciales sean correctas
- Confirma que la base de datos existe
- Revisa que el host sea `sql306.infinityfree.com`

### Páginas en blanco
- Activa `display_errors` en PHP
- Revisa los logs de error en el panel de InfinityFree
- Verifica que todos los archivos se subieron correctamente

## 📊 **MONITOREO**

### Verificaciones regulares:
1. **Espacio en disco:** Mantener bajo 4GB
2. **Uso de CPU:** Monitorear picos de uso
3. **Base de datos:** Limpiar registros antiguos
4. **Logs:** Revisar errores periódicamente

### Backup recomendado:
- Exportar base de datos semanalmente
- Descargar archivos importantes mensualmente
- Documentar cambios en configuración

## 🎯 **RESULTADO FINAL**

Una vez completado el despliegue, tu aplicación estará disponible en:
- **URL principal:** `http://tudominio.epizy.com/sistema_rh/public/`
- **Login:** `http://tudominio.epizy.com/sistema_rh/public/login.php`
- **Admin:** `http://tudominio.epizy.com/sistema_rh/public/admin.php`

## 📞 **SOPORTE**

Si encuentras problemas:
1. Revisa los logs de error en el panel de InfinityFree
2. Consulta la documentación de InfinityFree
3. Verifica que todos los pasos se completaron correctamente

¡Tu proyecto está listo para funcionar en InfinityFree! 🚀
