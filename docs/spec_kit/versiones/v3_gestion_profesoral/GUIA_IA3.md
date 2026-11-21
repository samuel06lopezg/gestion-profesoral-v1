# Guía de Interacción con la IA — v3: Autenticación, JWT y Roles (Gestión Profesoral)

## 1. Rol de la IA y Principios Rectores

En esta versión 3, la IA asiste en la construcción de la capa de seguridad y autenticación bajo las siguientes reglas inviolables:

1. **Acatamiento Estricto de la Constitución:**
   - **PHP 8.3 puro:** Prohibido sugerir `composer require`, librerías como `firebase/php-jwt` o frameworks (Slim, Laravel, Symfony).
   - **Tipado estricto:** `declare(strict_types=1);` como primera instrucción en cada archivo.
   - **Separación de Capas:** Controlador (HTTP) ➔ Servicio (Lógica y Reglas) ➔ Repositorio (PDO + SQL).
   - **Inyección Centralizada:** El archivo `servicios/ensamblador.php` es el único facultado para instanciar clases con `new`.
   - **Cero Secretos:** Toda clave o parámetro confidencial (`JWT_CLAVE`) se recupera mediante `getenv()`.

2. **Seguridad y Criptografía Nativa:**
   - La generación y validación del JWT se realiza mediante funciones nativas de hash y base64 URL-safe en `api_gestion/autorizacion/jwt.php`.
   - Las comparaciones de firmas y hashes criptográficos deben utilizar `hash_equals()`.
   - El hasheo y verificación de contraseñas debe realizarse exclusivamente con `password_hash($pass, PASSWORD_BCRYPT)` y `password_verify($pass, $hash)`.

3. **Mecanismo de Guardia y Códigos HTTP:**
   - Peticiones sin token o con token inválido/vencido deben responder **HTTP 401 Unauthorized** con cabecera `WWW-Authenticate: Bearer`.
   - Peticiones con token válido pero cuyos roles no autorizan la operación (ej: rol `consulta` intentando escribir, o no-admin accediendo a usuarios) deben responder **HTTP 403 Forbidden**.
   - Los errores de integridad referencial siguen respondiendo **HTTP 409 Conflict**.
   - Validaciones de cuerpo incorrecto responden **HTTP 422 Unprocessable Entity**.

4. **Frontend Desacoplado:**
   - El frontend `front_php` no tiene acceso a base de datos ni librerías PDO.
   - Toda comunicación con la API se realiza por HTTP con cURL pasando `Authorization: Bearer <token>`.
   - El estado de autenticación se gestiona en la superglobal `$_SESSION` del servidor PHP del front.

5. **No Regresión:**
   - Las 16 tablas de v1 y v2 deben mantenerse 100% funcionales tras envolver sus rutas en el guardia de seguridad.
