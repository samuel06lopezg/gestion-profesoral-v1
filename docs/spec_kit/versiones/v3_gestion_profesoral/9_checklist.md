# Lista de Chequeo de Cierre — v3: Autenticación, JWT y Roles

Esta lista de chequeo debe verificarse en su totalidad antes de realizar el commit de cierre y la creación del tag `v3`.

---

## 1. Verificación de la Constitución y Buenas Prácticas
- [ ] **Artículo 1 (Propósito Didáctico):** Todos los archivos nuevos (`jwt.php`, `guardia.php`, controladores, etc.) cuentan con cabecera explicativa en español.
- [ ] **Artículo 1.1 (Front Desacoplado):** El front no contiene `pdo_mysql`, no comparte archivos con la API y se comunica únicamente por HTTP.
- [ ] **Artículo 2 (PHP Puro):** Sin dependencias de Composer, sin librerías externas para JWT ni frameworks.
- [ ] **Artículo 3 (Arquitectura en 3 Capas):** Ningún controlador ejecuta SQL; ningún servicio maneja HTTP; las inyecciones se concentran en `ensamblador.php`.
- [ ] **Artículo 4 (Un solo comando):** `docker compose up -d --build` levanta el sistema completo con usuarios y roles precargados.
- [ ] **Artículo 6 (Borrado Lógico):** Los usuarios y roles se retiran con `activo = 0` y se filtran en todas las consultas con `activo = 1`.
- [ ] **Artículo 8 (Convenciones):** `declare(strict_types=1);` al inicio de cada archivo PHP. UTF-8 sin BOM.
- [ ] **Artículo 9 (Cero Secretos):** `JWT_CLAVE` y credenciales de BD se leen desde variables de entorno; `.env` en `.gitignore`; `.env.example` actualizado.

---

## 2. Pruebas Funcionales y Criterios de Aceptación
- [ ] **Login Exitoso:** `POST /api/login` con credenciales válidas retorna token JWT y datos de roles.
- [ ] **Prevención de Enumeración:** `POST /api/login` con credenciales inválidas devuelve HTTP 401 con mensaje genérico idéntico tanto para usuario inexistente como contraseña errónea.
- [ ] **Protección 401:** Cualquier petición a endpoints protegidos sin token devuelve HTTP 401 y cabecera `WWW-Authenticate: Bearer`.
- [ ] **Aislamiento de Rol Consulta:** Un usuario con rol `consulta` puede realizar consultas `GET`, pero al intentar `POST`, `PUT` o `DELETE` recibe HTTP 403 Forbidden.
- [ ] **Exclusividad Administrativa:** Solo el rol `admin` puede interactuar con `/api/usuario`, `/api/rol` y `/api/rol-usuario`. Otros roles reciben HTTP 403 Forbidden.
- [ ] **Hash Seguro:** Verificación en base de datos de que ningún registro en la columna `password` de `usuario` contenga texto plano; todos inician con `$2y$10$`.
- [ ] **Frontend Autenticado:**
  - Redirección automática a `/entrar` para peticiones anónimas.
  - La barra de navegación oculta opciones no autorizadas (p. ej. usuarios/roles para no-admin).
  - El botón "Cerrar sesión" destruye la sesión y redirige al login.
- [ ] **Regresión v1 y v2:** Todas las pruebas de las versiones previas pasan exitosamente al enviar el token correspondiente.
