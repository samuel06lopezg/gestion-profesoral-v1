# Especificación — v1: tablas sin clave foránea (PHP + MariaDB)

## 1. Qué construye esta versión

El CRUD completo, **API en PHP puro y pantalla en PHP**, cada una en su
contenedor, sobre **MariaDB**, de las 5 tablas del módulo que no tienen clave
foránea:

| Recurso | Tabla | Ruta de la API | Llave |
| --- | --- | --- | --- |
| Área de conocimiento | `area_conocimiento` | `/api/area-conocimiento` | `id` (texto, código como `1A01`) |
| Término clave | `termino_clave` | `/api/termino-clave` | `termino` (texto) |
| Línea de investigación | `linea_investigacion` | `/api/linea-investigacion` | `id` (entero, lo genera la base) |
| Programa | `programa` | `/api/programa` | `id` (entero, lo da quien crea) |
| Red académica | `red` | `/api/red` | `idr` (entero, lo da quien crea) |

## 2. Lo que esta versión NO incluye

- Las 11 tablas con FK (`docente`, `estudios_realizados`, …): llegan en la v2.
- Autenticación, JWT y roles. Las tablas `usuario`, `rol`, `rol_usuario` ya
  existen (esquema dado) pero la v1 no las nombra.
- Consultas de varias tablas, dashboard, informes.
- Reactivar un registro retirado: el borrado es lógico y la fila se queda, pero
  **no hay ruta para devolverla**. Nadie lo ha pedido todavía.
- Imagen corporativa propia (llega en la v4).

## 3. Las fichas

La columna `activo` **no aparece** en ninguna ficha a propósito: es de la base,
no del recurso (ver `5_data_model.md` §4).

### `area_conocimiento`
| Campo | Tipo | | Nota |
| --- | --- | --- | --- |
| `id` | texto, hasta 6 | obligatorio | Código, p. ej. `1A01` (ver C1) |
| `gran_area` | texto, hasta 60 | obligatorio | — |
| `area` | texto, hasta 60 | obligatorio | — |
| `disciplina` | texto, hasta 150 | obligatorio | Ver C2 |

### `termino_clave`
| Campo | Tipo | | Nota |
| --- | --- | --- | --- |
| `termino` | texto, hasta 30 | obligatorio | La llave es el propio término |
| `termino_ingles` | texto, hasta 30 | opcional | La traducción |

### `linea_investigacion`
| Campo | Tipo | | Nota |
| --- | --- | --- | --- |
| `id` | entero | — | Lo genera la base; **no viaja en el POST** |
| `nombre` | texto, hasta 45 | obligatorio | — |
| `descripcion` | texto, hasta 256 | obligatorio | — |

### `programa`
| Campo | Tipo | | Nota |
| --- | --- | --- | --- |
| `id` | entero | obligatorio | El código del programa, como `10101` |
| `nombre` | texto, hasta 150 | obligatorio | Ver C3 |
| `tipo` | texto, hasta 45 | obligatorio | — |
| `nivel` | texto, hasta 45 | obligatorio | — |
| `fecha_creacion` | texto, hasta 45 | obligatorio | TEXTO en el esquema dado, no fecha |
| `numero_cohortes` | texto, hasta 45 | obligatorio | — |
| `cant_graduados` | texto, hasta 45 | obligatorio | — |
| `fecha_actualizacion` | texto, hasta 45 | obligatorio | — |
| `ciudad` | texto, hasta 45 | obligatorio | — |
| `facultad` | entero | obligatorio | Número sin FK: la tabla `facultad` no existe en el módulo |
| `fecha_cierre` | texto, hasta 45 | opcional | Un programa abierto no tiene cierre |

### `red`
| Campo | Tipo | | Nota |
| --- | --- | --- | --- |
| `idr` | entero | obligatorio | La llave |
| `nombre` | texto, hasta 45 | obligatorio | — |
| `url` | texto, hasta 45 | obligatorio | Ver C4 |
| `pais` | texto, hasta 45 | obligatorio | — |

## 4. Requisitos funcionales (se repiten para cada uno de los 5 recursos)

Con `{r}` = el recurso y `{llave}` = su llave.

- **RF1 — Listar.** `GET /api/{r}` → **200** con el sobre
  `{tabla, limite, total, datos:[…]}`. Solo los activos. `limite` opcional
  (entero > 0, por defecto 1000). Sin filas activas → **204** sin cuerpo.
  `limite` <= 0 → **400**.
- **RF2 — Obtener uno.** `GET /api/{r}/{llave}` → **200** con la ficha.
  Inexistente **o retirado** → **404**.
- **RF3 — Crear.** `POST /api/{r}` con los campos obligatorios. Falta uno, o
  llega con el tipo equivocado → **422** con `errores[]`. Llave duplicada →
  **500** con el mensaje del motor en `detalle`.
- **RF4 — Reemplazar.** `PUT /api/{r}/{llave}` con **todos** los campos. La
  llave va en la ruta, no en el cuerpo. Falta uno → **422**.
- **RF5 — Actualizar.** `PATCH /api/{r}/{llave}` con **los que se quieran
  cambiar**. El mismo cuerpo que el PUT rechaza con 422, aquí responde 200.
  Cuerpo vacío `{}` → **400**, no 422.
- **RF6 — Retirar.** `DELETE /api/{r}/{llave}` marca `activo = FALSE`. Un
  segundo `DELETE` sobre la misma llave → **404**. La fila sigue en la base.
- **RF7 — Diagnóstico.** `GET /` → **200** con `{"version":"v1", "tablas":[…5]}`.

## 5. Criterios de aceptación

1. **Un solo comando.** `docker compose up -d --build` deja corriendo MariaDB
   (con la base y sus 19 tablas), la API y la pantalla. `GET
   http://localhost:8113/` responde `"version":"v1"`.
2. **Arranca CON DATOS.** `GET /api/programa` → **200** con `total: 191`;
   `GET /api/area-conocimiento` → **200** con `total: 218`.
3. **Las tablas sin carga inicial responden 204** en su listado
   (`termino-clave`, `linea-investigacion`, `red`) hasta que se cree la
   primera fila.
4. **Crear y listar** en cada uno de los 5 recursos: un `POST` válido responde
   200 y el `total` del listado sube en uno.
5. **El ciclo de los cinco verbos** en cada recurso: `POST` → `PUT` → `PATCH`
   de **un** campo → `GET` lo confirma → `DELETE`, y un segundo `DELETE` da
   **404**. Un `PUT` sin un obligatorio da **422**; el **mismo cuerpo** por
   `PATCH` da **200**.
6. **El borrado es LÓGICO, y se verifica.** Tras el `DELETE` el `total` baja en
   uno **y la fila sigue en la base** con `activo = 0` (consulta directa en
   MariaDB), en cada uno de los 5 recursos.
7. **La validación es la frontera.** Un `POST` sin un obligatorio → **422** con
   `errores:[…]`; una llave duplicada → **500**. En ninguno se escribe en la
   base.
8. **Prueba de capas.** `pruebas/prueba_capas.php` ejecuta los servicios con un
   **repositorio falso en memoria** y todas sus verificaciones pasan **con
   MariaDB apagada**.
9. **Cero secretos versionados.** `git grep -n "root"` no encuentra una clave
   escrita; existe `.env.example`; `.env` está en el `.gitignore`.

### Criterios de aceptación de la pantalla

| # | Criterio | Cómo se comprueba |
| --- | --- | --- |
| P1 | La pantalla muestra las filas **que dio la API** | Se le piden a la API y se buscan en el texto visible |
| P2 | Cada recurso tiene **dirección propia** | `/programas`, `/programas/nuevo`, `/programas/{id}/editar`; ídem para los otros 4. Ninguna con el nombre de la tabla como parámetro |
| P3 | Las hojas de estilo llegan como `text/css` | La pantalla se ve con estilos, no solo responde 200 |
| P4 | La pantalla **no habla en jerga** | Ni `PUT`, ni `PATCH`, ni `422`, ni `/api/`, ni el nombre del motor |
| P5 | Los **dos botones de guardar** se comportan distinto | "Guardar la ficha completa" rechaza un formulario a medias; "Guardar solo lo que cambié" lo acepta |
| P6 | Un error **no borra** lo que la persona escribió | Se reenvía el formulario con los valores puestos |
| P7 | **Con la API apagada la pantalla sigue en pie** | `docker compose stop api_gestion`: responde 200, con aviso y **sin un solo dato** |
| P8 | Sin filas, la pantalla dice que **todavía no hay** | Con `termino_clave` vacío: un recuadro, no un error |
| P9 | La palabra es **retirar**, no borrar | Se lee en los botones y mensajes |

## 6. Clarificaciones (compuerta 1)

> Decisiones **propuestas**, tomadas del ejemplo del curso. El equipo debe
> confirmarlas (o el profesor corregirlas) antes de pasar a `3_plan.md`.

| # | Duda | Decisión | Dónde |
| --- | --- | --- | --- |
| C1 | `area_conocimiento.id` está declarado `INT` y los datos del Excel son códigos como `1A01` | **Mandan los datos: `VARCHAR(6)`.** Si no, el script no puede cargar su propio catálogo | `db/init.sql` [C1] |
| C2 | `area_conocimiento.disciplina` es `VARCHAR(60)` y el valor más largo del catálogo es más largo | **Se agranda a `VARCHAR(150)`.** Recortar un catálogo oficial lo falsea | `db/init.sql` [C2] |
| C3 | `programa.nombre` es `VARCHAR(60)` en el script dado | **Se agranda a `VARCHAR(150)`** para no recortar nombres de programas | `db/init.sql` [C3] |
| C4 | `red.url` es `VARCHAR(45)` y una dirección web real suele ser más larga | **Se agranda a `VARCHAR(255)`** | `db/init.sql` [C4] |
| C5 | El esquema dado no tiene `activo` en las 16 tablas del módulo | **Ya está declarado** `activo BOOLEAN NOT NULL DEFAULT TRUE` en las tablas de nuestro `init.sql`; se documenta en `5_data_model.md` | `db/init.sql` [C5] |
| C6 | El catálogo del Excel trae errores de digitación | **Se corrigen al cargar** y cada corrección se anota en la cabecera de `init.sql` | `db/init.sql` [C6] |
| C7 | ¿Los otros recursos devuelven 204 con la tabla vacía igual que `programa`? | **Sí.** Es el mismo contrato para los 5 | `6_contracts.md` |
| C8 | ¿Qué código devuelve un `POST` exitoso? | **200**, como en el ejemplo de clase | `6_contracts.md` |
| C9 | `[NECESITA ACLARACIÓN: ¿los conteos de filas iniciales de `termino_clave`, `linea_investigacion` y `red` son 0?]` El módulo del curso solo da conteo para `area_conocimiento` (218) y `programa` (191) | Se asume **0** hasta que el profesor diga lo contrario | — |
