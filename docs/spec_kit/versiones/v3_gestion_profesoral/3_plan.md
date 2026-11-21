# Plan — v3: Autenticación, JWT y Control de Acceso por Roles (Gestión Profesoral)

## 1. Arquitectura y Componentes

```
┌─────────────────────────┐          ┌─────────────────────────┐          ┌─────────────────────────┐
│  front_php (puerto 8112)│  HTTP    │  api_gestion (8113)     │   PDO    │  mariadb (puerto 13331) │
│                         │          │                         │          │                         │
│  - Pantalla /entrar     │ ───────► │  - Enrutador index.php  │ ───────► │  - Tablas v1 + v2       │
│  - Cliente API con      │          │  - guardia.php (401/403)│          │  - Tablas v3:           │
│    Bearer Header        │          │  - jwt.php (HMAC-SHA256)│          │    * rol                │
│  - Control de sesión en │          │  - Controladores v3     │          │    * usuario            │
│    $_SESSION PHP        │          │  - Servicios v3         │          │    * rol_usuario        │
│  - Menú condicionado    │          │  - Repositorios v3      │          │  - Consultas preparadas │
│    por roles            │          │  - Hash BCRYPT          │          │    con password_hash    │
└─────────────────────────┘          └─────────────────────────┘          └─────────────────────────┘
```

---

## 2. Flujo del Guardia de Seguridad en la API

```
Petición Entrante
       │
       ▼
¿Es ruta pública? ────── SÍ ──────► Controlador (ej: POST /api/login)
       │ (NO)
       ▼
¿Viene encabezado Authorization: Bearer <token>?
       ├── NO ────────────────────► HTTP 401 Unauthorized (exigirSesion)
       └── SÍ ────────────────────► Validar firma HMAC-SHA256 y vigencia (exp)
                                          │
                   ┌──────────────────────┴──────────────────────┐
                   ▼                                             ▼
             Token Inválido / Vencido                      Token Válido
                   │                                             │
                   ▼                                             ▼
         HTTP 401 Unauthorized                         Extraer payload JWT:
                                                       ['sub' => username, 'roles' => [...]]
                                                                 │
                                                                 ▼
                                                  ¿Operación requiere Rol específico?
                                                  (ej: admin para usuarios/roles)
                                                       │
                                   ┌───────────────────┴───────────────────┐
                                   ▼                                       ▼
                             No tiene el rol                        Tiene el rol
                                   │                                       │
                                   ▼                                       ▼
                         HTTP 403 Forbidden                     ¿Es operación de escritura?
                     "Su rol no tiene permiso"                  (POST, PUT, DELETE)
                                                                           │
                                                       ┌───────────────────┴───────────────────┐
                                                       ▼                                       ▼
                                                 Rol 'consulta'                          Rol 'docente' o 'admin'
                                                       │                                       │
                                                       ▼                                       ▼
                                             HTTP 403 Forbidden                             Ejecutar
                                         "Su rol no tiene permiso"                       Controlador
```

---

## 3. Organización y Estructura del Código

### Componentes de la API (`api_gestion/`)

```
api_gestion/
├── index.php                             Enrutador con lista de rutas abiertas y guardias
├── autorizacion/
│   ├── jwt.php                          Codificación, decodificación y firma HMAC-SHA256
│   └── guardia.php                      exigirSesion() (401) y exigirRol() / exigirEscritura() (403)
├── modelos/
│   ├── Rol.php                          Entidad Rol
│   ├── Usuario.php                      Entidad Usuario (con password hasheado)
│   └── RolUsuario.php                   Entidad de asignación Usuario-Rol
├── excepciones/
│   ├── NoEncontradoExcepcion.php        404
│   └── ConflictoIntegridadExcepcion.php 409
├── repositorios/
│   ├── IRepositorioRol.php              Interfaz de persistencia para roles
│   ├── RepositorioRolMariaDB.php        Implementación MariaDB (PDO + Prepared Statements)
│   ├── IRepositorioUsuario.php          Interfaz de persistencia para usuarios
│   ├── RepositorioUsuarioMariaDB.php    Implementación MariaDB (búsqueda por username, CRUD)
│   ├── IRepositorioRolUsuario.php       Interfaz para vinculación usuario-rol
│   └── RepositorioRolUsuarioMariaDB.php Implementación MariaDB (roles por usuario, asignación)
├── servicios/
│   ├── ensamblador.php                  Contenedor de dependencias e inyección (único con new)
│   ├── IServicioSesion.php              Interfaz de lógica de autenticación
│   ├── ServicioSesion.php               Validación de credenciales, verify_password, emisión JWT
│   ├── IServicioRol.php                 Reglas de negocio para roles
│   ├── ServicioRol.php                  Lógica de roles
│   ├── IServicioUsuario.php             Reglas de negocio para usuarios (hasheo BCRYPT)
│   ├── ServicioUsuario.php              Lógica de usuarios
│   ├── IServicioRolUsuario.php          Reglas de asignación
│   └── ServicioRolUsuario.php           Lógica de asignación usuario-rol
└── controladores/
    ├── ControladorLogin.php             POST /api/login, GET /api/sesion, DELETE /api/sesion
    ├── ControladorRol.php               CRUD de /api/rol
    ├── ControladorUsuario.php           CRUD de /api/usuario
    └── ControladorRolUsuario.php        CRUD de /api/rol-usuario
```

### Componentes del Frontend (`front_php/`)

```
front_php/
├── index.php                             Enrutador web, middleware de sesión local y rutas de vista
├── cliente_api.php                       Wrapper cURL que adjunta Authorization: Bearer automáticamente
└── vistas/
    ├── plantilla.php                     Plantilla base con navegación condicionada según roles
    ├── entrar.php                        Formulario de inicio de sesión con username y password
    ├── inicio.php                        Página de bienvenida personalizada con nombre y roles del usuario
    ├── lista_usuarios.php                Vista administrativa para listar usuarios (solo admin)
    ├── formulario_usuario.php            Creación y edición de usuarios (solo admin)
    ├── lista_roles.php                   Vista administrativa para roles (solo admin)
    └── formulario_rol.php                Creación y edición de roles (solo admin)
```

---

## 4. Estrategia de Migración y Semillas en Base de Datos

En `db/init.sql`:
1. Asegurar que las tablas `rol`, `usuario` y `rol_usuario` posean índices únicos en `nombre` de rol, `username` y `email`.
2. Insertar los datos semilla (seeds) de roles esenciales:
   - `admin`: Administrador total del sistema.
   - `docente`: Gestión de información académica y profesoral.
   - `consulta`: Rol de solo lectura para auditoría o consulta.
3. Insertar usuarios iniciales con contraseñas hasheadas mediante BCRYPT (costo estándar 10):
   - `admin` (clave: `admin123`)
   - `docente1` (clave: `docente123`)
   - `consultor1` (clave: `consulta123`)
4. Asignar los roles correspondientes en `rol_usuario`.
