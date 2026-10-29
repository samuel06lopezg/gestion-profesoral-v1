# Lista de Chequeo y Criterios de Aceptación — v2

| # | Criterio de Aceptación | Estado | Método de Comprobación |
|---|---|---|---|
| 1 | Las tablas de la v1 tienen CRUD completo y su interfaz | Cumplido | Se ejecutan las operaciones de las 5 tablas v1 |
| 2 | Las tablas de la v2 tienen CRUD completo y su interfaz | Cumplido | Se verifica crear, editar, listar y retirar en las 5 tablas asignadas |
| 3 | Los cinco verbos del maestro-detalle pasan por procedimientos almacenados | Cumplido | El repositorio `RepositorioDocenteMariaDB` y `RepositorioEstudiosRealizadosMariaDB` invocan `CALL sp_*` con parámetros en `?` y `closeCursor()` |
| 4 | La relación maestro-detalle funciona de punta a punta: se abre el maestro, se le agregan renglones y se guardan en un solo envío | Cumplido | En `formulario_docente.php` se agregan renglones de estudios y se envían de forma atómica en un único POST transaccional |
| 5 | Cada clave foránea se elige de un desplegable cargado de la API | Cumplido | Los formularios renderizan `<select>` cargados desde `GET /api/docente` y muestran nombres completos en lugar de IDs |
| 6 | La clave foránea opcional acepta «(ninguna)» → null | Cumplido | En `formulario_docente.php`, `linea_investigacion_principal` vacía viaja como `null` nativo hacia la base |
| 7 | Crear un hijo con un padre inexistente responde 409 en castellano | Cumplido | Petición POST con `docente: 999999` responde `409 Conflict` (error 1452 capturado) |
| 8 | Regla de negocio de retiro de maestro: no se puede retirar docente con estudios | Cumplido | `DELETE /api/docente/{cedula}` con estudios activos responde 409 con mensaje `"No se puede retirar el docente porque tiene estudios registrados."` |
| 9 | El detalle se ve y se administra desde el maestro | Cumplido | Al abrir la edición o ficha del docente se muestran sus estudios realizados |
| 10 | El borrado es lógico y los listados filtran inactivos | Cumplido | Retirar un registro cambia `activo = 0`; desaparece de los listados y desplegables |
| 11 | La interfaz no habla en jerga técnica | Cumplido | Mensajes legibles en castellano para el usuario final sin códigos HTTP expuestos |
| 12 | La regresión de la v1 pasa completa | Cumplido | Todos los endpoints de la v1 siguen funcionando sin alteraciones |
