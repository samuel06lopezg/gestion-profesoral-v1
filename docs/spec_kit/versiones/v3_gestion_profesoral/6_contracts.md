# Contratos de API — v3: Autenticación, JWT y Control de Acceso por Roles (Gestión Profesoral)

Formato: JSON (`Content-Type: application/json; charset=utf-8`).

---

## 1. Endpoints de Autenticación y Sesión

### `POST /api/login` (Público)
Inicia sesión validando credenciales y emite un token JWT.

- **Cuerpo de la Petición:**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

- **Respuesta 200 OK (Credenciales válidas):**
```json
{
  "estado": 200,
  "mensaje": "Inicio de sesion exitoso.",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJhZG1pbiIsInJvbGVzIjpbImFkbWluIl0sImlhdCI6MTcwMDAwMDAwMCwiZXhwIjoxNzAwMDAzNjAwfQ.firma_hmac_sha256",
  "expira": 1700003600,
  "usuario": {
    "id": 1,
    "username": "admin",
    "email": "admin@universidad.edu.co",
    "nombre_completo": "Administrador del Sistema"
  },
  "roles": ["admin"]
}
```

- **Respuesta 401 Unauthorized (Credenciales incorrectas):**
```json
{
  "estado": 401,
  "mensaje": "El usuario o la contrasena no son correctos."
}
```

- **Respuesta 422 Unprocessable Entity (Campos faltantes o vacíos):**
```json
{
  "estado": 422,
  "mensaje": "Datos de entrada invalidos.",
  "errores": [
    "El campo username es obligatorio.",
    "El campo password es obligatorio."
  ]
}
```

---

### `GET /api/sesion` (Protegido - Requiere `Authorization: Bearer <token>`)
Devuelve la información de identidad extraída del token firmado.

- **Respuesta 200 OK:**
```json
{
  "estado": 200,
  "username": "admin",
  "roles": ["admin"],
  "expira": 1700003600
}
```

- **Respuesta 401 Unauthorized (Sin token o token no válido):**
```json
{
  "estado": 401,
  "mensaje": "No hay una sesion valida. Inicie sesion.",
  "detalle": "Falta la cabecera Authorization: Bearer <token>."
}
```

---

### `DELETE /api/sesion` (Protegido)
Indica al cliente el cierre de sesión y la instrucción de purgar el token almacenado.

- **Respuesta 200 OK:**
```json
{
  "estado": 200,
  "mensaje": "Sesion cerrada. Borre el token en el cliente.",
  "username": "admin"
}
```

---

## 2. Gestión de Usuarios: `/api/usuario` (Exclusivo Rol `admin`)

### `GET /api/usuario`
Lista todos los usuarios activos del sistema con sus roles asociados.

- **Respuesta 200 OK:**
```json
{
  "tabla": "usuario",
  "total": 3,
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@universidad.edu.co",
      "nombre_completo": "Administrador del Sistema",
      "activo": 1,
      "roles": ["admin"]
    },
    {
      "id": 2,
      "username": "docente1",
      "email": "docente1@universidad.edu.co",
      "nombre_completo": "Profesor Carlos Gómez",
      "activo": 1,
      "roles": ["docente"]
    }
  ]
}
```

- **Respuesta 403 Forbidden (Si quien consulta no es `admin`):**
```json
{
  "estado": 403,
  "mensaje": "Su rol no tiene permiso para esta operacion.",
  "detalle": "El recurso exige rol de administrador."
}
```

---

### `POST /api/usuario`
Crea un nuevo usuario encriptando su contraseña con BCRYPT.

- **Cuerpo de la Petición:**
```json
{
  "username": "profesor_nuevo",
  "password": "claveSegura123",
  "email": "nuevo@universidad.edu.co",
  "nombre_completo": "Pedro Pérez"
}
```

- **Respuesta 201 Created:**
```json
{
  "estado": 201,
  "mensaje": "Usuario creado correctamente.",
  "datos": {
    "id": 4,
    "username": "profesor_nuevo",
    "email": "nuevo@universidad.edu.co",
    "nombre_completo": "Pedro Pérez",
    "activo": 1
  }
}
```

- **Respuesta 409 Conflict (Username o email duplicado):**
```json
{
  "estado": 409,
  "mensaje": "Conflicto de integridad.",
  "detalle": "El nombre de usuario o correo electronico ya se encuentra registrado."
}
```

---

### `PUT /api/usuario/{id}`
Actualiza datos de un usuario (permite cambiar nombre, email, o contraseña si se envía).

---

### `DELETE /api/usuario/{id}`
Retiro lógico (`activo = 0`).

- **Respuesta 200 OK:**
```json
{
  "estado": 200,
  "mensaje": "Usuario retirado correctamente.",
  "id": 4
}
```

---

## 3. Gestión de Roles: `/api/rol` (Exclusivo Rol `admin`)

### `GET /api/rol`
- **Respuesta 200 OK:**
```json
{
  "tabla": "rol",
  "total": 3,
  "datos": [
    { "id": 1, "nombre": "admin", "descripcion": "Administrador total", "activo": 1 },
    { "id": 2, "nombre": "docente", "descripcion": "Docente del modulo", "activo": 1 },
    { "id": 3, "nombre": "consulta", "descripcion": "Solo lectura", "activo": 1 }
  ]
}
```

---

## 4. Asignación de Roles: `/api/rol-usuario` (Exclusivo Rol `admin`)

### `POST /api/rol-usuario`
Asigna un rol a un usuario.

- **Cuerpo de la Petición:**
```json
{
  "usuario_id": 2,
  "rol_id": 1
}
```

- **Respuesta 201 Created:**
```json
{
  "estado": 201,
  "mensaje": "Rol asignado correctamente al usuario.",
  "datos": {
    "usuario_id": 2,
    "rol_id": 1
  }
}
```

---

### `DELETE /api/rol-usuario`
Desvincula un rol de un usuario.

- **Cuerpo de la Petición:**
```json
{
  "usuario_id": 2,
  "rol_id": 1
}
```

- **Respuesta 200 OK:**
```json
{
  "estado": 200,
  "mensaje": "Rol desvinculado correctamente del usuario."
}
```

---

## 5. Respuestas de Seguridad Transversales

### Petición sin token en cualquier recurso protegido
- **Código:** `401 Unauthorized`
- **Cabecera:** `WWW-Authenticate: Bearer`
- **Cuerpo:**
```json
{
  "estado": 401,
  "mensaje": "No hay una sesion valida. Inicie sesion.",
  "detalle": "Falta la cabecera Authorization: Bearer <token>."
}
```

### Intento de escritura (POST/PUT/DELETE) por Rol `consulta`
- **Código:** `403 Forbidden`
- **Cuerpo:**
```json
{
  "estado": 403,
  "mensaje": "Su rol no tiene permiso para esta operacion.",
  "detalle": "El rol consulta solo tiene permisos de lectura (GET)."
}
```
