# Investigación — v3: Autenticación, JWT y Control de Acceso por Roles (PHP 8.3 Puro)

## 1. Implementación de JSON Web Tokens (JWT) sin Dependencias Externas

### ¿Por qué sin librerías de Composer como `firebase/php-jwt`?
1. **Constitución del Proyecto (Artículo 2):** Se exige PHP puro, sin framework y sin Composer. El entorno de ejecución no incluye carpeta `vendor/` ni dependencias descargadas por gestores de paquetes.
2. **Didáctica y Transparencia:** Un JWT bajo el estándar RFC 7519 es una estructura conceptualmente simple formada por tres bloques en Base64 URL-safe separados por puntos (`encabezado.carga.firma`):
   - **Encabezado:** Algoritmo y tipo (`{"alg":"HS256","typ":"JWT"}`).
   - **Carga (Payload):** Datos públicos del sujeto (`sub`, `roles`, `iat`, `exp`).
   - **Firma:** Cifrado HMAC con función hash SHA-256 (`hash_hmac('sha256', "$encabezado.$carga", $clave, true)`).
3. **Seguridad Nativa con `hash_equals`:** Al validar la firma del token recibido frente a la firma esperada calculada por el servidor, es indispensable utilizar `hash_equals()` en lugar del operador de igualdad estándar `===`. `hash_equals()` ejecuta una comparación en tiempo constante, previniendo ataques de temporización (*timing attacks*) que podrían revelar la clave o firma por variaciones en microsegundos de CPU.

### Codificación Base64 URL-Safe (RFC 7515)
El formato base64 convencional contiene caracteres incompatibles con URLs y encabezados HTTP (`+`, `/`, `=`):
- El carácter `+` se transforma en `-`.
- El carácter `/` se transforma en `_`.
- Los caracteres de relleno `=` se suprimen con `rtrim(..., '=')`.
Al decodificar, se restituyen los caracteres y se completa la longitud a múltiplo de 4 mediante relleno de signos `=`.

---

## 2. Almacenamiento y Verificación de Contraseñas

### Algoritmo BCRYPT mediante `password_hash()`
En cumplimiento del requerimiento estricto del proyecto:
- **Jamás se almacenan contraseñas en texto plano** ni con funciones vulnerables como MD5 o SHA-1.
- Se utiliza la función nativa de PHP `password_hash($password, PASSWORD_BCRYPT)`. BCRYPT incorpora sal (*salt*) criptográfica aleatoria generada automáticamente por llamada y un costo adaptativo de cómputo (por defecto 10), mitigando ataques de fuerza bruta y tablas arcoíris (*rainbow tables*).
- La validación en el inicio de sesión se efectúa mediante `password_verify($passwordIngresada, $hashAlmacenado)`, la cual es inmune a ataques de temporización y maneja la extracción transparente de la sal embebida en el hash.

---

## 3. Modelo de Control de Acceso Basado en Roles (RBAC) con 3 Tablas

### Diferencia con el proyecto de clase `proyecto_php4`
En el proyecto de facturación de referencia, el control de acceso utilizaba tablas dinámicas de rutas y permisos (`ruta`, `rutarol`) cruzadas mediante un procedimiento almacenado.
En el módulo de **Gestión Profesoral**, la especificación define un modelo directo de 3 tablas:
- `rol` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`)
- `usuario` (`id`, `username`, `password`, `email`, `nombre_completo`, `activo`, `fecha_creacion`, `fecha_actualizacion`)
- `rol_usuario` (`usuario_id`, `rol_id`)

### Resolución de Permisos en la API
1. Los roles asignados al usuario se extraen directamente de la base de datos durante la emisión del token y se embeben en la carga del JWT como un array de strings:
   `"roles": ["admin"]` o `"roles": ["docente"]` o `"roles": ["consulta"]`.
2. El guardia de la API opera en dos escalones complementarios:
   - **Autenticación (401):** Comprueba que la cabecera `Authorization` provea un JWT válido emitido por el sistema y no vencido.
   - **Autorización por Recurso (403):** 
     - Las rutas administrativas de `/api/usuario`, `/api/rol` y `/api/rol-usuario` exigen pertenencia al rol `admin`. Si el usuario no tiene dicho rol, se aborta la petición con **HTTP 403 Forbidden**.
     - Las operaciones de modificación (**POST**, **PUT**, **DELETE**) en cualquier recurso exigen que el usuario no sea de rol exclusivo `consulta`. Si el usuario posee únicamente el rol `consulta`, se deniega con **HTTP 403 Forbidden**.

---

## 4. Estrategia de Sesión en el Frontend Desacoplado

### Aislamiento de Procesos (Artículo 1.1 de la Constitución)
El contenedor `front_php` no tiene conexión a la base de datos ni librerías de PDO. Su interacción con la API se produce exclusivamente vía HTTP con cURL:
1. El usuario envía sus credenciales al controlador del front en `POST /entrar`.
2. El front invoca mediante cURL el endpoint `POST http://api_gestion:8113/api/login`.
3. Al recibir respuesta exitosa (200), el front almacena el `token`, el `username` y la lista de `roles` en la superglobal `$_SESSION` de PHP.
4. En cada petición posterior a la API, el cliente cURL del front extrae el token de la sesión e inyecta la cabecera:
   `Authorization: Bearer <token>`
5. Si la API responde 401 (token expirado), el front destruye la sesión y redirige al usuario a la vista de login con un mensaje explicativo.
