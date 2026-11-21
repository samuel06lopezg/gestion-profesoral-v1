# Desglose de Tareas — v3: Autenticación, JWT y Control de Acceso por Roles

## Fase 1: Base de Datos y Semillas de Seguridad (`db/`)
- [x] **T-01:** Verificar integridad de tablas `rol`, `usuario` y `rol_usuario` en `db/init.sql`.
- [x] **T-02:** Agregar datos semilla (seeds) de roles (`admin`, `docente`, `consulta`) en `db/init.sql`.
- [x] **T-03:** Generar e insertar usuarios iniciales con hashes BCRYPT reales (`admin`, `docente1`, `consultor1`).
- [x] **T-04:** Insertar relaciones iniciales en `rol_usuario`.

## Fase 2: Módulo Criptográfico y Guardia de Autorización (`api_gestion/autorizacion/`)
- [x] **T-05:** Implementar `jwt.php` con funciones `jwt_clave()`, `jwt_firmar()` y `jwt_leer()` usando HMAC-SHA256 y Base64 URL-safe nativo.
- [x] **T-06:** Implementar `guardia.php` con funciones `tokenDeLaCabecera()`, `responderYSalir()`, `exigirSesion()` (401), `exigirRol()` (403) y `exigirEscritura()` (403 para rol consulta).

## Fase 3: Capa de Persistencia (Modelos y Repositorios en `api_gestion/`)
- [x] **T-07:** Crear modelos de dominio: `Rol.php`, `Usuario.php`, `RolUsuario.php`.
- [x] **T-08:** Crear interfaz e implementación de `IRepositorioRol.php` y `RepositorioRolMariaDB.php`.
- [x] **T-09:** Crear interfaz e implementación de `IRepositorioUsuario.php` y `RepositorioUsuarioMariaDB.php` (búsqueda por username y email).
- [x] **T-10:** Crear interfaz e implementación de `IRepositorioRolUsuario.php` y `RepositorioRolUsuarioMariaDB.php`.

## Fase 4: Capa de Servicios y Lógica de Negocio (`api_gestion/servicios/`)
- [x] **T-11:** Crear `IServicioSesion.php` y `ServicioSesion.php` (validación de username, `password_verify()`, emisión de JWT con roles).
- [x] **T-12:** Crear servicios para roles y usuarios (`ServicioRol.php`, `ServicioUsuario.php`, `ServicioRolUsuario.php`).
- [x] **T-13:** Actualizar `ensamblador.php` para registrar e inyectar todas las dependencias nuevas de seguridad.

## Fase 5: Capa de Controladores y Enrutamiento Protegido (`api_gestion/`)
- [x] **T-14:** Crear `ControladorLogin.php` con métodos `entrar()` (`POST /api/login`), `quienSoy()` (`GET /api/sesion`) y `salir()` (`DELETE /api/sesion`).
- [x] **T-15:** Crear controladores CRUD: `ControladorUsuario.php`, `ControladorRol.php`, `ControladorRolUsuario.php`.
- [x] **T-16:** Actualizar `api_gestion/index.php`:
  - Declarar lista blanca de rutas públicas (`POST /api/login`, `GET /`).
  - Proteger todas las rutas restantes con `exigirSesion()`.
  - Proteger rutas de escritura con `exigirEscritura()`.
  - Proteger rutas `/api/usuario`, `/api/rol`, `/api/rol-usuario` con `exigirRol('admin')`.

## Fase 6: Frontend Web (`front_php/`)
- [x] **T-17:** Actualizar `cliente_api.php` en el front para extraer el JWT de `$_SESSION['token']` y adjuntar `Authorization: Bearer <token>`.
- [x] **T-18:** Crear vista y formulario de inicio de sesión en `front_php/vistas/entrar.php`.
- [x] **T-19:** Implementar rutas web en `front_php/index.php`: `GET /entrar`, `POST /entrar`, `GET /salir`.
- [x] **T-20:** Adaptar `front_php/vistas/plantilla.php` para renderizar el menú dinámico por roles y el botón de cerrar sesión.
- [x] **T-21:** Crear vistas administrativas para listar y crear usuarios y roles (solo accesibles para admin).

## Fase 7: Pruebas y Validación
- [x] **T-22:** Crear script de pruebas de humo `pruebas_humo/humo_v3.sh` o `probar_v3.ps1`.
- [x] **T-23:** Ejecutar pruebas de regresión de las 16 tablas de v1 y v2.
- [x] **T-24:** Validar cumplimiento de todos los criterios de aceptación de la v3.
