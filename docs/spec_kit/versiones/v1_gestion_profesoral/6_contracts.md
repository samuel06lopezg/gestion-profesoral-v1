# Contratos — v1: tablas sin clave foránea

Cada ruta con todos sus desenlaces, errores incluidos. Los 5 recursos comparten
**el mismo contrato**; solo cambian la ruta, la llave y los campos (ver
`2_spec.md` §3).

## 0. Reglas comunes

- Todo en JSON, UTF-8. Los nombres de campo son los de las columnas.
- **Sobre de error plano y en español**, sin envolturas:

```json
{ "estado": 422, "mensaje": "Datos inválidos.", "errores": ["El campo «nombre» es obligatorio."] }
```
```json
{ "estado": 404, "mensaje": "No existe el programa 99999.", "detalle": "…" }
```

- `estado` repite el código HTTP; `mensaje` es para personas; `detalle` y
  `errores` aparecen solo cuando aplican.
- La llave de la ruta **no** viaja en el cuerpo del `PUT`/`PATCH`.
- Un valor del tipo equivocado (texto donde va un entero) → **422**.

## 1. Las rutas

| Recurso | Colección | Un registro |
| --- | --- | --- |
| Área de conocimiento | `/api/area-conocimiento` | `/api/area-conocimiento/{id}` |
| Término clave | `/api/termino-clave` | `/api/termino-clave/{termino}` |
| Línea de investigación | `/api/linea-investigacion` | `/api/linea-investigacion/{id}` |
| Programa | `/api/programa` | `/api/programa/{id}` |
| Red académica | `/api/red` | `/api/red/{idr}` |

Una llave de texto (`termino`) que lleve espacios o tildes se envía codificada
(`rawurlencode`).

## 2. Operaciones (con `{r}` = el recurso)

### GET `/` — diagnóstico
| Código | Cuerpo |
| --- | --- |
| 200 | `{"mensaje":"…","version":"v1","tablas":["programa","area_conocimiento","termino_clave","linea_investigacion","red"]}` |

### GET `/api/{r}` — listar
Parámetro opcional `?limite=N`.

| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Hay filas activas | `{"tabla":"programa","limite":1000,"total":191,"datos":[{…},…]}` |
| 204 | No hay filas activas | *(sin cuerpo)* |
| 400 | `limite` <= 0 | sobre de error |
| 500 | Falla inesperada | sobre de error |

### GET `/api/{r}/{llave}` — obtener uno
| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Existe y está activo | la ficha |
| 404 | No existe **o** está retirado | sobre de error |

### POST `/api/{r}` — crear
| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Cuerpo válido | la ficha creada |
| 422 | Falta un obligatorio o un tipo es incorrecto | sobre con `errores[]` |
| 500 | Llave duplicada | sobre con el mensaje del motor en `detalle` |

En `linea-investigacion` el `id` **no** viaja: lo genera la base y vuelve en la
respuesta.

### PUT `/api/{r}/{llave}` — reemplazar
| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Cuerpo con **todos** los campos | la ficha |
| 404 | No existe o está retirado | sobre de error |
| 422 | Falta cualquier campo obligatorio | sobre con `errores[]` |

### PATCH `/api/{r}/{llave}` — actualizar
| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Cuerpo con **uno o más** campos válidos | la ficha |
| 400 | Cuerpo vacío `{}` | sobre de error |
| 404 | No existe o está retirado | sobre de error |
| 422 | Un campo trae un tipo incorrecto | sobre con `errores[]` |

> **El contraste que se evalúa:** el mismo cuerpo `{"ciudad":"Medellín"}` da
> **422** por `PUT` y **200** por `PATCH`.

### DELETE `/api/{r}/{llave}` — retirar
| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| 200 | Estaba activo; ahora `activo = FALSE` | `{"estado":200,"mensaje":"…"}` |
| 404 | No existe o ya estaba retirado | sobre de error |

La fila **no** se borra de la base.

## 3. Ejemplo — `programa`

`POST /api/programa`
```json
{
  "id": 9001, "nombre": "Ingeniería de Sistemas", "tipo": "Pregrado",
  "nivel": "Profesional", "fecha_creacion": "2001-02-01",
  "numero_cohortes": "40", "cant_graduados": "1200",
  "fecha_actualizacion": "2026-09-01", "ciudad": "Medellín", "facultad": 3
}
```
Respuesta **200** con la ficha (el opcional `fecha_cierre` llega `null`).
