# Guía de Inicio Rápido (Quickstart) — v3: Autenticación y Roles

## 1. Requisitos Previos
- Docker Engine y Docker Compose instalados.
- Puerto **8112** (Front), **8113** (API) y **13331** (MariaDB) disponibles.
- Archivo `.env` configurado a partir de `.env.example`.

---

## 2. Variables de Entorno Requeridas (`.env`)

Copie `.env.example` a `.env` y ajuste los valores (el archivo `.env` no se versiona):
```env
DB_NOMBRE=gestion_profesoral
DB_USUARIO=root
DB_CLAVE=cambie_esta_clave

# Seguridad JWT (Artículo 9: cero secretos en código). JWT_CLAVE es obligatoria.
JWT_CLAVE=una-clave-larga-y-aleatoria
JWT_MINUTOS=120
```
Los puertos son fijos en `docker-compose.yml`: front 8112, API 8113, MariaDB 13331.

---

## 3. Puesta en Marcha con Docker

```bash
# 1. Detener contenedores previos asegurando recreación limpia
docker compose down -v

# 2. Construir e iniciar en segundo plano
docker compose up -d --build

# 3. Verificar estado de los servicios
docker compose ps
```

---

## 4. Usuarios de Prueba Preconfigurados

| Username | Contraseña | Rol asignado | Capacidades |
|---|---|---|---|
| `admin` | `admin123` | `admin` | Acceso total: lectura, escritura y administración de usuarios y roles |
| `docente1` | `docente123` | `docente` | Gestión académica: lectura y escritura en catálogos y tablas del docente |
| `consultor1` | `consulta123` | `consulta` | Solo lectura (`GET`): bloqueado para cualquier creación, edición o retiro |

---

## 5. Pruebas Rápidas con `curl` o PowerShell

### A. Iniciar Sesión (Obtener JWT)
```bash
# Iniciar sesión como Administrador
curl -X POST http://localhost:8113/api/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}'
```
*Copie el valor del campo `token` devuelto.*

### B. Probar Endpoint Protegido con Token
```bash
# Consultar identidad de sesión
curl -X GET http://localhost:8113/api/sesion \
  -H "Authorization: Bearer <TOKEN_OBTENIDO>"
```

### C. Probar Rechazo por Falta de Token (401)
```bash
# Intento sin token
curl -X GET http://localhost:8113/api/docente
# Respuesta esperada: HTTP 401 Unauthorized
```

### D. Probar Rechazo de Escritura para Rol Consulta (403)
```bash
# 1. Iniciar sesión como consultor1
curl -X POST http://localhost:8113/api/login \
  -H "Content-Type: application/json" \
  -d '{"username": "consultor1", "password": "consulta123"}'

# 2. Intentar crear una línea de investigación con el token de consulta
curl -X POST http://localhost:8113/api/linea-investigacion \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN_CONSULTOR>" \
  -d '{"nombre": "Línea No Permitida", "descripcion": "Prueba de rechazo 403"}'
# Respuesta esperada: HTTP 403 Forbidden
```

### E. Probar Acceso a Usuarios (Solo Admin)
```bash
# Intentar listar usuarios con token de docente1
curl -X GET http://localhost:8113/api/usuario \
  -H "Authorization: Bearer <TOKEN_DOCENTE>"
# Respuesta esperada: HTTP 403 Forbidden
```

---

## 6. Pruebas en el Navegador Web (Frontend)

1. Abra en su navegador: `http://localhost:8112/`
2. Si no tiene sesión, será redirigido a `http://localhost:8112/entrar`.
3. Inicie sesión con `admin` / `admin123`:
   - Verifique que la barra de navegación muestre la pestaña **"Usuarios"** y **"Roles"**.
4. Cierre sesión (`/salir`) e inicie sesión con `consultor1` / `consulta123`:
   - Verifique que **NO** aparece la pestaña de "Usuarios" ni "Roles".
   - Intente navegar a cualquier tabla y compruebe que no se muestran botones de "Crear" ni "Retirar".

---

## 7. Pruebas de humo automatizadas

Con el sistema levantado:
```bash
./pruebas_humo/humo_v3.sh            # API: login, 401/403 por rol, CRUD de los 16 recursos
python3 pruebas_humo/humo_front.py   # Front: sesión, menú por rol, formularios, logout
./pruebas_humo/humo_v2.sh            # Regresión v2 (ahora inicia sesión como admin)
```
