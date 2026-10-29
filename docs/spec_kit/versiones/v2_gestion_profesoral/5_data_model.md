# Modelo de Datos — v2: Tablas con FK y Procedimientos Almacenados (Gestión Profesoral)

Base de datos: `gestion_profesoral` (MariaDB 11).

## 1. Las 5 Tablas del Alcance Asignado

```
docente (cedula PK, nombres, apellidos, genero, cargo, fecha_nacimiento, correo,
         telefono, url_cvlac, fecha_actualizacion, escalafon, perfil,
         cat_minciencia?, conv_minciencia, nacionalidaad,
         linea_investigacion_principal? FK, activo)

estudios_realizados (id PK AUTO_INCREMENT, titulo, universidad, fecha, tipo,
                     ciudad, docente FK, ins_acreditada, metodologia,
                     perfil_egresado, pais, activo)

evaluacion_docente (id PK AUTO_INCREMENT, calificacion, semestre, docente FK, activo)

experiecia (id PK AUTO_INCREMENT, nombre_cargo, institucion, tipo,
            fecha_inicio, fecha_fin?, docente FK, activo)

reconocimiento (id PK AUTO_INCREMENT, tipo, fecha, institucion, nombre,
                ambito, docente FK, activo)
```

## 2. Tipos y Restricciones

| Tabla | Columna | Tipo Final | Nulo | Descripción / Regla |
| --- | --- | --- | --- | --- |
| `docente` | `cedula` | `INT` | No | PK. Número de identificación ciudadana |
| | `nombres` | `VARCHAR(60)` | No | Nombres del docente |
| | `apellidos` | `VARCHAR(60)` | No | Apellidos del docente |
| | `genero` | `VARCHAR(12)` | No | Género |
| | `cargo` | `VARCHAR(30)` | No | Cargo actual |
| | `fecha_nacimiento` | `DATE` | No | Fecha de nacimiento |
| | `correo` | `VARCHAR(70)` | No | Correo electrónico |
| | `telefono` | `VARCHAR(20)` | No | Teléfono de contacto |
| | `url_cvlac` | `VARCHAR(128)` | No | Enlace CVLAC Minciencias |
| | `fecha_actualizacion`| `DATE` | No | Fecha de última actualización |
| | `escalafon` | `VARCHAR(45)` | No | Escalafón profesoral |
| | `perfil` | `LONGTEXT` | No | Perfil académico y profesional |
| | `cat_minciencia` | `VARCHAR(45)` | **Sí** | Categoría Minciencias (opcional) |
| | `conv_minciencia` | `VARCHAR(45)` | No | Convocatoria Minciencias |
| | `nacionalidaad` | `VARCHAR(45)` | No | Nacionalidad (columna histórica) |
| | `linea_investigacion_principal` | `INT` | **Sí** | FK a `linea_investigacion(id)`. Admite null |
| | `activo` | `BOOLEAN` | No | Borrado lógico (default `TRUE`) |
| `estudios_realizados` | `id` | `INT AUTO_INCREMENT` | No | PK autoincremental (ver C7) |
| | `titulo` | `VARCHAR(45)` | No | Título obtenido |
| | `universidad` | `VARCHAR(50)` | No | Universidad otorgante |
| | `fecha` | `DATE` | No | Fecha de grado |
| | `tipo` | `VARCHAR(45)` | No | Nivel/Tipo (Pregrado, Maestría, etc.) |
| | `ciudad` | `VARCHAR(45)` | No | Ciudad del estudio |
| | `docente` | `INT` | No | FK a `docente(cedula)` |
| | `ins_acreditada` | `TINYINT` | No | 1 = Acreditada, 0 = No acreditada |
| | `metodologia` | `VARCHAR(45)` | No | Modalidad (Presencial, Virtual, etc.)|
| | `perfil_egresado` | `LONGTEXT` | No | Descripción del perfil |
| | `pais` | `VARCHAR(45)` | No | País |
| | `activo` | `BOOLEAN` | No | Borrado lógico |
| `evaluacion_docente` | `id` | `INT AUTO_INCREMENT` | No | PK |
| | `calificacion` | `FLOAT` | No | Nota de evaluación |
| | `semestre` | `VARCHAR(45)` | No | Periodo académico (ej. 2026-1) |
| | `docente` | `INT` | No | FK a `docente(cedula)` |
| | `activo` | `BOOLEAN` | No | Borrado lógico |
| `experiecia` | `id` | `INT AUTO_INCREMENT` | No | PK |
| | `nombre_cargo` | `VARCHAR(45)` | No | Cargo desempeñado |
| | `institucion` | `VARCHAR(45)` | No | Empresa o institución |
| | `tipo` | `VARCHAR(45)` | No | Tipo de experiencia (Docente, etc.) |
| | `fecha_inicio` | `DATE` | No | Fecha de inicio |
| | `fecha_fin` | `DATE` | **Sí** | Fecha fin (nulo si es cargo actual) |
| | `docente` | `INT` | No | FK a `docente(cedula)` |
| | `activo` | `BOOLEAN` | No | Borrado lógico |
| `reconocimiento` | `id` | `INT AUTO_INCREMENT` | No | PK |
| | `tipo` | `VARCHAR(45)` | No | Distinción, mención, etc. |
| | `fecha` | `DATE` | No | Fecha del reconocimiento |
| | `institucion` | `VARCHAR(45)` | No | Entidad que lo otorga |
| | `nombre` | `VARCHAR(45)` | No | Nombre del reconocimiento |
| | `ambito` | `VARCHAR(45)` | No | Institucional, Nacional, Internacional |
| | `docente` | `INT` | No | FK a `docente(cedula)` |
| | `activo` | `BOOLEAN` | No | Borrado lógico |

## 3. Cambios al Esquema (Declarados y Numerados)

| # | Cambio | Motivo |
| --- | --- | --- |
| C7 | `estudios_realizados.id`: `INT NOT NULL` → `INT NOT NULL AUTO_INCREMENT` | Permite la inserción dinámica y fluida de renglones de estudios en el formulario maestro-detalle sin forzar la digitación de un identificador numérico artificial. |
| C8 | Procedimientos almacenados para el maestro-detalle (`docente` y `estudios_realizados`) | Requisito v2: las operaciones pasan por Stored Procedures con parámetros en `?` e incorporan la regla de negocio para impedir el retiro de docentes con estudios activos. |

## 4. Procedimientos Almacenados Creados

1. `sp_listar_docente()`: Obtiene docentes activos (`activo = 1`).
2. `sp_consultar_docente(IN p_cedula INT)`: Retorna un docente por cédula.
3. `sp_crear_docente(...)`: Inserta un docente y retorna la fila guardada.
4. `sp_actualizar_docente(...)`: Actualiza los datos del docente y retorna la fila.
5. `sp_eliminar_docente(IN p_cedula INT)`: Verifica si hay estudios activos. Si existen, ejecuta `SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede retirar el docente porque tiene estudios registrados.';`. De lo contrario, `UPDATE docente SET activo = 0 WHERE cedula = p_cedula;`.
6. `sp_listar_estudios_realizados()`: Lista todos los estudios activos.
7. `sp_consultar_estudio_realizado(IN p_id INT)`: Retorna un estudio por ID.
8. `sp_listar_estudios_por_docente(IN p_docente INT)`: Retorna los estudios activos de un docente.
9. `sp_crear_estudio_realizado(...)`: Inserta un estudio y retorna la fila creada.
10. `sp_actualizar_estudio_realizado(...)`: Actualiza un estudio y retorna la fila.
11. `sp_eliminar_estudio_realizado(IN p_id INT)`: Actualiza `activo = 0` para el estudio.
