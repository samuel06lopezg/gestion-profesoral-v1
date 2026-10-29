# Especificación — v2: Maestro-Detalle y Tablas con Claves Foráneas (Gestión Profesoral)

## 1. Qué construye esta versión (Alcance asignado)

Esta versión construye el CRUD completo (API REST en PHP 8.3 puro + Frontend PHP con plantillas renderizadas en el servidor) de las tablas asignadas del módulo **Gestión Profesoral**, con persistencia en **MariaDB 11**:

1. **Relación Maestro-Detalle:**
   - **Maestro:** `docente` (clave primaria `cedula`).
   - **Detalle:** `estudios_realizados` (clave foránea `docente` hacia `docente.cedula`).
   - **Procedimientos Almacenados:** El CRUD completo del maestro-detalle se realiza exclusivamente a través de Stored Procedures MariaDB (`sp_*`).
   - **Envío único:** Desde la interfaz gráfica, al crear o editar un `docente`, se agregan, editan y gestionan dinámicamente sus `estudios_realizados` en una misma pantalla y se envían de forma atómica en un único POST transaccional.

2. **Tablas Dependientes con Claves Foráneas (CRUD Corriente):**
   - `evaluacion_docente` (FK `docente` hacia `docente.cedula`).
   - `experiecia` (FK `docente` hacia `docente.cedula`).
   - `reconocimiento` (FK `docente` hacia `docente.cedula`).

3. **Regla de Integridad de Negocio Estricta:**
   - **No se puede retirar un docente si tiene estudios registrados y activos.**
   - Si se intenta retirar un docente con estudios activos, la base de datos o el servicio rechaza la operación disparando `SIGNAL SQLSTATE '45000'` / excepción de integridad, respondiendo con código **HTTP 409 Conflict** y mensaje:
     `"No se puede retirar el docente porque tiene estudios registrados."`

4. **Desplegables en el Frontend:**
   - Ninguna clave foránea se digita a mano: se consumen mediante `<select>` cargados desde la API (`GET /api/docente`, `GET /api/linea-investigacion`).
   - Muestran nombres legibles (p. ej. `Juan Pérez`) y envían la clave (`cedula`).
   - Claves foráneas opcionales (`docente.linea_investigacion_principal`) admiten la opción «(ninguna)» que envía `null` (no `0` ni cadena vacía).

5. **Manejo de Errores de Integridad Referencial:**
   - Errores de integridad (1452, 1062, 1451, SQLSTATE '45000') responden **HTTP 409 Conflict** en castellano claro, **nunca HTTP 500**.

6. **Regresión:**
   - Todas las funcionalidades de la v1 (`area_conocimiento`, `termino_clave`, `linea_investigacion`, `programa`, `red`) continúan operativas y pasando sus pruebas.

---

## 2. Lo que esta versión NO incluye
- Autenticación, JWT, inicio de sesión y control de roles (`usuario`, `rol`, `rol_usuario` pertenecen a la v3).
- Consultas multitabla complejas y dashboard analítico (pertenecen a la v4).
- Tablas asignadas a otros integrantes del equipo (`docente_departamento`, `intereses_futuros`, `red_docente`, `estudio_ac`, `apoyo_profesoral`, `beca`).

---

## 3. Fichas de Recursos

### Recurso: `docente` (Maestro)
- **Ruta API:** `/api/docente`
- **Llave primaria:** `cedula` (entero, provisto al crear).
- **Campos:**
  - `cedula` (int, obligatorio)
  - `nombres` (varchar 60, obligatorio)
  - `apellidos` (varchar 60, obligatorio)
  - `genero` (varchar 12, obligatorio)
  - `cargo` (varchar 30, obligatorio)
  - `fecha_nacimiento` (date, obligatorio)
  - `correo` (varchar 70, obligatorio)
  - `telefono` (varchar 20, obligatorio)
  - `url_cvlac` (varchar 128, obligatorio)
  - `fecha_actualizacion` (date, obligatorio)
  - `escalafon` (varchar 45, obligatorio)
  - `perfil` (longtext, obligatorio)
  - `cat_minciencia` (varchar 45, opcional)
  - `conv_minciencia` (varchar 45, obligatorio)
  - `nacionalidaad` (varchar 45, obligatorio)
  - `linea_investigacion_principal` (int FK nullable, opcional)
  - `estudios` (array opcional de registros de `estudios_realizados` para inserción atómica)

### Recurso: `estudios-realizados` (Detalle)
- **Ruta API:** `/api/estudios-realizados`
- **Llave primaria:** `id` (int, autoincremental).
- **Campos:**
  - `id` (int, generado por BD)
  - `titulo` (varchar 45, obligatorio)
  - `universidad` (varchar 50, obligatorio)
  - `fecha` (date, obligatorio)
  - `tipo` (varchar 45, obligatorio)
  - `ciudad` (varchar 45, obligatorio)
  - `docente` (int FK a `docente.cedula`, obligatorio)
  - `ins_acreditada` (tinyint 1 o 0, obligatorio)
  - `metodologia` (varchar 45, obligatorio)
  - `perfil_egresado` (longtext, obligatorio)
  - `pais` (varchar 45, obligatorio)

### Recurso: `evaluacion-docente`
- **Ruta API:** `/api/evaluacion-docente`
- **Llave primaria:** `id` (int, autoincremental).
- **Campos:**
  - `calificacion` (float, obligatorio)
  - `semestre` (varchar 45, obligatorio)
  - `docente` (int FK a `docente.cedula`, obligatorio)

### Recurso: `experiencia` (Tabla `experiecia`)
- **Ruta API:** `/api/experiencia`
- **Llave primaria:** `id` (int, autoincremental).
- **Campos:**
  - `nombre_cargo` (varchar 45, obligatorio)
  - `institucion` (varchar 45, obligatorio)
  - `tipo` (varchar 45, obligatorio)
  - `fecha_inicio` (date, obligatorio)
  - `fecha_fin` (date, opcional)
  - `docente` (int FK a `docente.cedula`, obligatorio)

### Recurso: `reconocimiento`
- **Ruta API:** `/api/reconocimiento`
- **Llave primaria:** `id` (int, autoincremental).
- **Campos:**
  - `tipo` (varchar 45, obligatorio)
  - `fecha` (date, obligatorio)
  - `institucion` (varchar 45, obligatorio)
  - `nombre` (varchar 45, obligatorio)
  - `ambito` (varchar 45, obligatorio)
  - `docente` (int FK a `docente.cedula`, obligatorio)

---

## 4. Requisitos Funcionales de la v2

- **RF1 — Listado y Filtrado Lógico:** Listar únicamente registros con `activo = 1`.
- **RF2 — Creación Maestro-Detalle Atómica:** Permite crear un docente junto con sus estudios en un único POST a `/api/docente` con rollback en caso de fallo.
- **RF3 — Consulta Maestro con Detalle:** Consultar un docente devuelve sus datos básicos y la lista de sus estudios realizados activos.
- **RF4 — Restricción de Retiro de Maestro:** `DELETE /api/docente/{cedula}` verifica si existen estudios realizados activos. Si existen, responde **HTTP 409 Conflict** con mensaje: *"No se puede retirar el docente porque tiene estudios registrados."*
- **RF5 — Desplegables de Claves Foráneas:** Todo formulario con FK despliega un `<select>` alimentado por la API que muestra descripciones legibles y envía claves primarias numéricas.
- **RF6 — Claves Foráneas Opcionales:** Admite valor nulo en `linea_investigacion_principal` al seleccionar «(ninguna)».
- **RF7 — Integridad Referencial HTTP 409:** Intentos de insertar claves foráneas inexistentes o duplicadas responden HTTP 409, nunca 500.
