# Plan de Tareas — v2: Maestro-Detalle y Tablas con FK (Gestión Profesoral)

## Fase 1: Base de Datos y Procedimientos Almacenados
- [x] T1.1 Actualizar `db/init.sql` con el ajuste de autoincremento para `estudios_realizados.id` (C7).
- [x] T1.2 Implementar procedimientos almacenados para `docente`:
  - `sp_listar_docente`
  - `sp_consultar_docente`
  - `sp_crear_docente`
  - `sp_actualizar_docente`
  - `sp_eliminar_docente` (con regla SIGNAL SQLSTATE '45000' si tiene estudios activos).
- [x] T1.3 Implementar procedimientos almacenados para `estudios_realizados`:
  - `sp_listar_estudios_realizados`
  - `sp_consultar_estudio_realizado`
  - `sp_listar_estudios_por_docente`
  - `sp_crear_estudio_realizado`
  - `sp_actualizar_estudio_realizado`
  - `sp_eliminar_estudio_realizado`.

## Fase 2: Backend API REST (`api_gestion`)
- [x] T2.1 Crear excepción `ConflictoIntegridadExcepcion.php` y módulo de traducción `errores_de_integridad.php`.
- [x] T2.2 Modelos de dominio: `Docente.php`, `EstudiosRealizados.php`, `EvaluacionDocente.php`, `Experiencia.php`, `Reconocimiento.php`.
- [x] T2.3 Repositorios e interfaces:
  - `IRepositorioDocente` y `RepositorioDocenteMariaDB` (vía Stored Procedures con `closeCursor`).
  - `IRepositorioEstudiosRealizados` y `RepositorioEstudiosRealizadosMariaDB` (vía Stored Procedures con `closeCursor`).
  - Repositorios para `evaluacion_docente`, `experiecia`, `reconocimiento`.
- [x] T2.4 Servicios de dominio e interfaces:
  - `IServicioDocente` y `ServicioDocente` (soporte de transacción única maestro-detalle y validación de regla de retiro).
  - Servicios para los 4 recursos restantes.
  - Registrar en `ensamblador.php`.
- [x] T2.5 Controladores:
  - `ControladorDocente`, `ControladorEstudiosRealizados`, `ControladorEvaluacionDocente`, `ControladorExperiencia`, `ControladorReconocimiento`.
- [x] T2.6 Rutas en `api_gestion/index.php`:
  - Registrar los 5 recursos con soporte para GET, POST, PUT, PATCH, DELETE y códigos de respuesta correspondientes (200, 204, 400, 404, 409, 422).

## Fase 3: Frontend PHP (`front_php`)
- [x] T3.1 Métodos de cliente HTTP en `front_php/cliente_api.php` para los 5 recursos.
- [x] T3.2 Rutas y despachador en `front_php/index.php`.
- [x] T3.3 Vistas maestro-detalle:
  - `formulario_docente.php`: Tabla dinámica de estudios con agregar/remover filas y envío paralelo `detalle_*[]`.
  - Desplegable `<select>` para `linea_investigacion_principal` con «(ninguna)» -> null.
  - `lista_docentes.php` con listado, visualización de estudios y acción de retiro.
- [x] T3.4 Vistas de tablas con FK:
  - Formularios y listados para `estudios_realizados`, `evaluacion_docente`, `experiecia`, `reconocimiento` con `<select>` de docentes activos mostrando nombres legibles.
- [x] T3.5 Actualización de navegación en `plantilla.php`.

## Fase 4: Pruebas y Validación
- [x] T4.1 Pruebas de humo automatizadas en `pruebas_humo/`.
- [x] T4.2 Verificación de los 12 criterios de aceptación de la v2.
- [x] T4.3 Regresión completa de los recursos v1.
