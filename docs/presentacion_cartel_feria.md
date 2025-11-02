# RH-NEXUS: Sistema de Gestión de Recursos Humanos con Inteligencia Artificial

## 1. Introducción

**RH-NEXUS** es un sistema integral de gestión de recursos humanos diseñado para optimizar los procesos de reclutamiento, selección y administración de personal mediante la integración de inteligencia artificial.

### Objetivos del Proyecto
- Automatizar y optimizar el proceso completo de reclutamiento de personal
- Integrar evaluación inteligente de candidatos mediante IA
- Centralizar la gestión de usuarios, solicitudes y estructura organizacional
- Reducir tiempos de selección y mejorar la calidad de contrataciones
- Proporcionar herramientas de análisis y seguimiento para toma de decisiones

---

## 2. Problema/Hipótesis

Los procesos tradicionales de reclutamiento presentan ineficiencias: tiempos prolongados generando costos elevados y pérdida de talento, subjetividad basada en impresiones personales más que criterios objetivos, falta de estandarización produciendo inconsistencias y sesgos, y dificultades para gestionar múltiples solicitudes simultáneamente. La hipótesis plantea que un sistema automatizado con inteligencia artificial para evaluación y ranking de candidatos, junto con digitalización completa de procesos administrativos, mejorará significativamente la eficiencia reduciendo tiempos de selección en al menos un 40% mediante automatización de evaluaciones iniciales. Un algoritmo objetivo basado en criterios medibles aumentará objetividad eliminando subjetividades y sesgos, mientras que centralización y flujo automatizado reducirá errores mejorando comunicación entre roles, facilitando identificación candidatos mediante ranking.

---

## 3. Metodología

Se aplicó metodología ágil en tres etapas: desarrollo del sistema de gestión de recursos humanos con arquitectura MVC, implementación de reclutamiento inteligente con integración de IA para evaluación y ranking de candidatos, y optimización del flujo de aprobaciones.

**Herramientas y técnicas**
- Backend y frontend: PHP 8.0+ y tecnologías web (HTML5, CSS3, JavaScript, Bootstrap 5).
- IA: API REST con cURL y API keys.
- Base de datos: MySQL con PDO.
- Documentación: PlantUML y Git.

Esta metodología aseguró escalabilidad, seguridad y funcionalidad en la solución propuesta.

---

## 4. Desarrollo y Tecnología

### Stack Tecnológico

**Backend:**
- PHP 8.0+ (Programación orientada a objetos)
- PDO (PHP Data Objects) para acceso a base de datos
- Arquitectura MVC con separación de capas
- Middleware de autenticación y autorización
- Sistema de sesiones seguro con protección contra fuerza bruta

**Base de Datos:**
- MySQL con normalización relacional
- Índices optimizados para consultas eficientes
- Transacciones para integridad de datos

**Frontend:**
- HTML5 semántico
- CSS3 con diseño responsive (Bootstrap 5)
- JavaScript vanilla para interacciones dinámicas
- AJAX para actualizaciones asíncronas

**Inteligencia Artificial:**
- API REST para evaluación de candidatos
- Normalización de datos para análisis consistente
- Algoritmo de compatibilidad (100 puntos: escolaridad, experiencia, carrera, competencias)
- Sistema de ranking automático basado en puntajes

**Infraestructura:**
- Servidor web Apache con mod_rewrite
- HTTPS forzado para seguridad
- Alojamiento InfinityFree con adaptación dinámica

### Características Principales
- Autenticación multi-rol (Admin, RH, Gerente, Jefe de Área)
- Gestión completa de solicitudes de personal con flujo de aprobación
- Sistema de candidatos con evaluación automática y manual
- Cálculo de compatibilidad entre requisitos y perfil del candidato
- Ranking inteligente mediante IA
- Sistema de notificaciones en tiempo real
- Gestión organizacional (Sedes y Departamentos)
- Dashboard personalizado por rol

---

## 5. Figuras

El proyecto incluye documentación visual completa mediante diagramas PlantUML:

### Diagramas Disponibles
- **Diagramas de Casos de Uso**: 11 diagramas por módulo del sistema
- **Diagramas de Secuencia**: 11 diagramas de interacciones temporales
- **Diagramas de Clases**: 10 diagramas de estructura del sistema
- **Diagramas de Estados**: 5 diagramas de ciclos de vida
- **Diagramas de Actividades**: 13 diagramas de procesos de negocio
- **Diagramas de Bloques**: 6 diagramas de arquitectura del sistema
- **Diagramas de Colaboración**: 5 diagramas de interacciones entre objetos
- **Diagramas de Flujo**: 11 diagramas de procesos paso a paso
- **Diagramas de Base de Datos**: 4 diagramas del modelo relacional

### Figuras Clave Recomendadas para Cartel
1. **Diagrama de Arquitectura General** - Visión completa del sistema
2. **Diagrama de Flujo de Reclutamiento** - Proceso principal del sistema
3. **Diagrama de Bloques del Sistema de IA** - Arquitectura de inteligencia artificial
4. **Diagrama de Casos de Uso Completo** - Funcionalidades principales
5. **Diagrama de Estados de Solicitudes** - Ciclo de vida de solicitudes

---

## 6. Resultados

### Resultados Obtenidos

**Funcionalidad:**
- ✅ Sistema completamente operativo con 8 módulos principales
- ✅ Integración exitosa de servicio de IA para evaluación de candidatos
- ✅ Sistema multi-usuario con 4 roles diferenciados
- ✅ Gestión completa del ciclo de reclutamiento (creación → contratación)

**Tecnología:**
- ✅ Arquitectura escalable y mantenible
- ✅ Sistema seguro con protección contra ataques comunes
- ✅ Interfaz intuitiva y responsive
- ✅ Base de datos normalizada y optimizada

**Documentación:**
- ✅ 75+ diagramas UML generados
- ✅ Diccionarios de datos y clases completos
- ✅ Documentación técnica exhaustiva
- ✅ CRC Cards para todas las clases principales

### Comparación con Hipótesis
- **Objetivo de reducción de tiempos**: Sistema optimiza evaluación automática
- **Objetividad en selección**: Algoritmo de compatibilidad basado en criterios objetivos
- **Gestión centralizada**: Todos los procesos digitalizados y accesibles
- **Mejora continua**: Sistema de ranking y métricas para análisis

---

## 7. Conclusiones / Trabajo Futuro

El sistema RH-NEXUS demuestra que la integración de inteligencia artificial acelera significativamente el proceso de evaluación inicial de candidatos, mientras que el algoritmo de compatibilidad proporciona criterios objetivos y reproducibles que eliminan subjetividades tradicionales. La digitalización completa elimina redundancias y errores manuales, mejorando la eficiencia operativa del departamento de recursos humanos. La arquitectura modular permite adaptación a diferentes organizaciones, ofreciendo una herramienta poderosa para optimizar tiempos de reclutamiento. Para la organización, los datos centralizados facilitan análisis y toma de decisiones estratégicas, mientras que para los candidatos el proceso resulta más ágil y transparente. El sistema contribuye como modelo replicable para la industria mediante metodología de integración de IA en procesos de selección y documentación exhaustiva como referencia técnica. Como trabajo futuro, se contempla implementar sistema de reportes y analytics avanzados, integración con portales de empleo para recepción automática de CVs, dashboard de métricas en tiempo real y aplicación móvil para acceso desde dispositivos móviles. A mediano plazo se integrará con sistemas de nómina y contabilidad, se desarrollará módulo de evaluación de desempeño post-contratación y sistema de capacitación. A largo plazo se implementará aprendizaje automático para mejora continua del algoritmo, análisis predictivo de rotación de personal, integración con servicios cloud y arquitectura de microservicios para mayor escalabilidad, consolidando RH-NEXUS como plataforma integral de gestión de recursos humanos.

---

## Resumen Ejecutivo para Cartel

**RH-NEXUS** es un sistema integral de gestión de recursos humanos que automatiza el proceso de reclutamiento mediante inteligencia artificial. Integra evaluación automática de candidatos, gestión de solicitudes, estructura organizacional y sistema de notificaciones en una plataforma web moderna y segura. El sistema reduce tiempos de selección, aumenta objetividad en decisiones y proporciona herramientas de análisis para optimizar procesos de contratación.

**Tecnologías**: PHP 8.0, MySQL, JavaScript, Bootstrap, API REST (IA)
**Resultados**: Sistema operativo con 8 módulos, 75+ diagramas UML, integración IA exitosa
**Impacto**: Optimización de procesos de RH, mejora en calidad de contrataciones

---

*Documento generado para presentación en feria de proyectos - Sistema RH-NEXUS*
