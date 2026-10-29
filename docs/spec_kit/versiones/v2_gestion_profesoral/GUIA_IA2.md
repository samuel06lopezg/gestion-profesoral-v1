# Guía de Interacción con la IA — v2: Maestro-Detalle y Tablas con FK

## 1. Rol de la IA y Principios Rectores
En esta versión, la IA actúa como asistente de codificación en modo pair-programming sujeto a:
1. **Constitución del Proyecto:** Respetar la separación estricta de capas (Controlador → Servicio → Repositorio), inyección de dependencias exclusiva en `ensamblador.php`, tipado estricto `declare(strict_types=1);` y prohibición total de frameworks externos o librerías de terceros (PHP 8.3 puro).
2. **Procedimientos Almacenados en MariaDB:** Obligatorios para las operaciones del maestro-detalle (`docente` y `estudios_realizados`), con parámetros preparados con `?` y cierre de cursores con `closeCursor()`.
3. **Manejo de Errores de Integridad Referencial:** Mapear violaciones de FK (1452), claves duplicadas (1062) y `SIGNAL SQLSTATE '45000'` a respuestas HTTP **409 Conflict**, previniendo caídas con error 500.
4. **Regla de Negocio del Maestro:** Impedir el retiro lógico del docente si tiene estudios activos registrados.
5. **No Regresión de la v1:** Mantener íntegros y funcionales los cinco recursos de la versión anterior.

## 2. Prompts de Trabajo y Correcciones Realizadas

Ver el archivo adjunto [prompts_v2.md](file:///c:/Users/User/Documents/POE/Proyecto_gestion_profesoral/Gestion_Profesoral/docs/spec_kit/versiones/v2_gestion_profesoral/prompts_v2.md) donde se documentan los requerimientos textuales del usuario y las decisiones arquitectónicas tomadas para satisfacerlos.
