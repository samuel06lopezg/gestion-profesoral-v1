# Especificación — v3: Autenticación, JWT y Control de Acceso por Roles (Gestión Profesoral)

## 1. Qué construye esta versión (Alcance de la v3)

Esta versión incorpora la capa de seguridad, autenticación y autorización basada en roles (RBAC - Role-Based Access Control) sobre el sistema existente del módulo **Gestión Profesoral**, manteniendo la persistencia en **MariaDB 11** y la arquitectura desacoplada de API REST (PHP 8.3 puro) y Frontend Web (PHP Server-Rendered):

1. **Gestión de Identidades y Roles (3 Tablas):**
   - `rol`: Catálogo de roles del sistema (`admin`, `docente`, `consulta`), con identificador, nombre único, descripción y estado activo.
   - `usuario`: Usuarios del sistema con identificador, `username` único, contraseña encriptada (con `password_hash()` BCRYPT), `email` único, nombre completo, estado activo y marcas temporales.
   - `rol_usuario`: Tabla de enlace muchos a muchos entre usuarios y roles (`usuario_id`, `rol_id`).

2. **Autenticación sin Librerías Externas (JWT Artesanal):**
   - `POST /api/login`: Endpoint público que recibe credenciales (`username` y `password`), valida la existencia del usuario y la validez del hash de la contraseña mediante `password_verify()`.
   - Genera y retorna un JSON Web Token (JWT) firmado con algoritmo HMAC-SHA256 (`HS256`) implementado en PHP puro (RFC 7519 / RFC 7515).
   - El payload del JWT contiene el identificador y `username` del usuario (`sub`), la lista de nombres de sus roles asignados (`roles`), fecha de emisión (`iat`) y fecha de caducidad (`exp`).
   - Cero secretos en código: La clave de firma se obtiene exclusivamente mediante variable de entorno `JWT_SECRET` / `JWT_CLAVE`.

3. **Mecanismo de Guardia y Autorización en la API (Middleware RBAC):**
   - **Guardia de Sesión (`exigirSesion()`):** Inspecciona el encabezado HTTP `Authorization: Bearer <token>`. Si el token falta, está vencido, está truncado o su firma HMAC no coincide con `hash_equals()`, rechaza de inmediato con **HTTP 401 Unauthorized** y encabezado `WWW-Authenticate: Bearer`.
   - **Guardia de Roles y Permisos (`exigirRol([...])`):** Verifica que el usuario autenticado cuente con los privilegios exigidos para la operación:
     - **Rol `admin`:** Acceso total (lectura y escritura a todos los recursos del módulo v1, v2 y v3; acceso exclusivo al CRUD de `usuario`, `rol` y `rol_usuario`).
     - **Rol `docente`:** Acceso de lectura y escritura a los recursos académicos y del módulo profesoral (`docente`, `estudios_realizados`, `evaluacion_docente`, etc.), pero sin acceso a administración de usuarios ni roles.
     - **Rol `consulta`:** Acceso exclusivo de solo lectura (**HTTP GET**). Cualquier intento de modificación (**POST**, **PUT**, **DELETE**) es rechazado terminantemente con **HTTP 403 Forbidden** y cuerpo estructurado con el mensaje:
       `"Su rol no tiene permiso para esta operacion."`

4. **CRUD de Usuarios, Roles y Asignación (Exclusivo Administrador):**
   - Endpoints completos para administración de usuarios (`/api/usuario`), roles (`/api/rol`) y vinculación de roles (`/api/rol-usuario`).
   - Solo accesibles por usuarios con rol `admin`. Si un usuario con rol `docente` o `consulta` intenta consumirlos, recibe **HTTP 403 Forbidden**.

5. **Frontend Adaptado a Roles y Sesión:**
   - Pantalla de inicio de sesión (`/entrar` o `/login`) con formulario de `username` y `password`.
   - Manejo de sesión desacoplada en el frontend mediante `$_SESSION['token']`, `$_SESSION['usuario']` y `$_SESSION['roles']`. El frontend consume la API enviando la cabecera `Authorization: Bearer <token>`.
   - Barra de navegación y menú dinámico según rol: la sección "Usuarios y Roles" solo es visible para el rol `admin`.
   - Botón de "Cerrar sesión" (`/salir`) que destruye la sesión local y redirige al login.
   - Redirección automática al login si un usuario no autenticado intenta navegar en el sistema.

6. **Regresión Completa:**
   - Todas las funcionalidades de la v1 (5 catálogos independientes) y de la v2 (Maestro-Detalle `docente`/`estudios_realizados` y tablas dependientes con FK) continúan 100% operativas bajo la protección del guardia de seguridad.

---

## 2. Lo que esta versión NO incluye

- Segundo motor de base de datos relacional (en este módulo se mantiene MariaDB 11 según el diseño del caso de aula).
- Consultas multitabla analíticas complejas de 4+ tablas (pertenecen a la v4).
- Dashboard analítico con gráficos interactivos Chart.js / SVG (pertenece a la v4).
- Manual de marca institucional y PWA / diseño responsive avanzado (pertenecen a la v4).
- Despliegue en producción en la nube (AlwaysData / VPS) (pertenece a la v4).

---

## 3. Matriz de Control de Acceso por Roles (RBAC)

| Recurso / Operación | Método HTTP | Público | Rol `consulta` | Rol `docente` | Rol `admin` |
|---|---|:---:|:---:|:---:|:---:|
| `POST /api/login` | POST | **Permitido** | Permitido | Permitido | Permitido |
| `GET /api/sesion` | GET | 401 | **Permitido** | **Permitido** | **Permitido** |
| `DELETE /api/sesion` (logout) | DELETE | 401 | **Permitido** | **Permitido** | **Permitido** |
| Catálogos v1 (`area`, `linea`, `programa`, etc.) | GET | 401 | **Permitido** | **Permitido** | **Permitido** |
| Catálogos v1 (`area`, `linea`, `programa`, etc.) | POST / PUT / DELETE | 401 | **403 Forbidden** | **Permitido** | **Permitido** |
| Maestro-Detalle y FK v2 (`docente`, `estudios`, etc.) | GET | 401 | **Permitido** | **Permitido** | **Permitido** |
| Maestro-Detalle y FK v2 (`docente`, `estudios`, etc.) | POST / PUT / DELETE | 401 | **403 Forbidden** | **Permitido** | **Permitido** |
| Gestión de Usuarios (`/api/usuario`) | GET / POST / PUT / DELETE | 401 | **403 Forbidden** | **403 Forbidden** | **Permitido** |
| Gestión de Roles (`/api/rol`) | GET / POST / PUT / DELETE | 401 | **403 Forbidden** | **403 Forbidden** | **Permitido** |
| Asignación de Roles (`/api/rol-usuario`) | GET / POST / DELETE | 401 | **403 Forbidden** | **403 Forbidden** | **Permitido** |

---

## 4. Criterios de Aceptación de la Versión 3

1. **CA-01 (Autenticación Exitosa):** Al enviar credenciales válidas a `POST /api/login`, la API responde HTTP 200 con token JWT válido, expiración en segundos/minutos y datos básicos del usuario y sus roles.
2. **CA-02 (Rechazo de Credenciales Inválidas):** Al enviar usuario inexistente o contraseña errónea, la API responde HTTP 401 con mensaje genérico `"El usuario o la contrasena no son correctos"`, sin revelar si el usuario existe o no (prevención de enumeración de usuarios).
3. **CA-03 (Protección contra Falta de Token):** Cualquier petición a un endpoint protegido sin cabecera `Authorization: Bearer <token>` responde HTTP 401 Unauthorized y cabecera `WWW-Authenticate: Bearer`.
4. **CA-04 (Protección contra Token Inválido o Vencido):** Peticiones con tokens manipulados o expirados son rechazadas con HTTP 401 Unauthorized sin revelar detalles internos.
5. **CA-05 (Aislamiento de Rol Consulta):** Un usuario autenticado con rol `consulta` puede listar y consultar todos los registros (GET), pero al intentar crear, editar o retirar (POST, PUT, DELETE) en cualquier recurso académico recibe HTTP 403 Forbidden.
6. **CA-06 (Restricción de Recursos de Administración):** Los endpoints de usuarios y roles (`/api/usuario`, `/api/rol`, `/api/rol-usuario`) son totalmente inaccesibles para roles `consulta` y `docente`, respondiendo HTTP 403 Forbidden. Solo el rol `admin` puede gestionarlos.
7. **CA-07 (Contraseñas Hasheadas):** En la base de datos jamás existe una contraseña en texto plano; todas las contraseñas se almacenan mediante `password_hash($password, PASSWORD_BCRYPT)`.
8. **CA-08 (Menú Dinámico en Frontend):** La interfaz web muestra u oculta las opciones del menú según los roles en sesión. Un usuario sin rol `admin` no ve en su barra de navegación el acceso a Usuarios ni a Roles.
9. **CA-09 (Cierre de Sesión):** Al presionar "Cerrar Sesión", la sesión del cliente se elimina por completo y la siguiente petición redirige al formulario de inicio de sesión.
10. **CA-10 (No Regresión):** Los 16 recursos previos (v1 y v2) funcionan correctamente al consumirse con un token que posea permisos de lectura o escritura.
