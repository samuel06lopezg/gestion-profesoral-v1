# Registro de Prompts y Decisiones — Versión 2

## Prompt 1: Requerimientos del módulo y relación maestro-detalle
**Instrucción del usuario:**
> "esto son todos los requerimientos necesarios para la v2, la v1 por el momento dejemosla asi, ya que esa entrega ya esta calificada, revisa estos requerimientos y te mando las tablas que me corresponden a mi en la v2 ya que es un trabajo por grupos. adicionalmente quiero adelantarte algo, y es que la relacion maestro detalle sea con docente y estudios realizados"

**Análisis e impacto:**
- Se fija la relación Maestro-Detalle principal: `docente` (maestro) y `estudios_realizados` (detalle).
- Se confirma que el CRUD del maestro-detalle debe implementarse utilizando procedimientos almacenados en MariaDB.
- Los campos de detalle se envían en un único formulario atómico (`detalle_*[]`).

---

## Prompt 2: Asignación de tablas y regla de integridad de negocio
**Instrucción del usuario:**
> "me corresponden estas tablas, algo que quiero que tengas presente es que en el maestro detalle no se puede eliminar el docente si tiene registrado estudios"
> Adjuntos:
> - Imágenes con la asignación: `docente` (maestro), `estudios_realizados`, `evaluacion_docente`, `experiecia`, `reconocimiento` (detalles y tablas dependientes).
> - Reglas v2: procedimientos almacenados para maestro-detalle y claves foráneas en el front desplegadas como `<select>` de HTML.

**Análisis e impacto:**
- Se implementan los 5 recursos asignados: `docente`, `estudios_realizados`, `evaluacion_docente`, `experiecia`, `reconocimiento`.
- Regla de negocio innegociable: **Un docente no se puede retirar si tiene estudios registrados activos**.
- Se implementa esta regla a dos niveles:
  1. Base de datos: Procedimiento `sp_eliminar_docente` ejecuta `SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede retirar el docente porque tiene estudios registrados.';` si `SELECT COUNT(*) FROM estudios_realizados WHERE docente = p_cedula AND activo = 1` es mayor a cero.
  2. Servicio PHP: Validación de dominio previa en `ServicioDocente::retirar()`.
- Ambos niveles aseguran que la API responda con **HTTP 409 Conflict** y un mensaje descriptivo en castellano.
- Desplegables en el front: `<select>` con opciones traídas de `GET /api/docente` y `GET /api/linea-investigacion` (con opción «(ninguna)» -> null).
