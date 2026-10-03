# Metodología de trabajo del equipo — Módulo de Gestión Profesoral (PHP)

> Este documento resume **cómo trabaja nuestro equipo**. No reemplaza al del
> curso (`ProyectosDeAula/docs/0_METODOLOGIA.md`, que "manda sobre todo lo
> demás"): lo aplica a nuestro módulo. Si algo de aquí contradice al del
> curso, gana el del curso.

## 1. El equipo

| Dato | Valor |
| --- | --- |
| Módulo | Gestión Profesoral (`gestion_profesoral`, 19 tablas) |
| Stack | PHP 8.3 puro + PDO + MariaDB, sin framework y sin Composer |
| Integrante(s) | Samuel — `<completar con los demás integrantes>` |
| Encargado del `main` (integrador) | `<completar>` |
| Profesor | `ccastro2050` (colaborador en AMBOS repositorios) |

## 2. El método: SDD por versiones

Primero la especificación, después el código, versión por versión.

1. **La especificación manda.** No se programa nada que la spec de la versión
   en curso no pida.
2. **No se anticipa.** Nada de una versión futura se construye "de una vez"
   (ni JWT en la v1, ni dashboard en la v2).
3. **Una versión está terminada** solo cuando pasan sus criterios de
   aceptación → commit + **tag `vN`** en `main` → solo entonces se escribe la
   spec de la siguiente.
4. **Una versión cerrada no se reabre.** Los ajustes van en la siguiente.
5. **Regresión obligatoria.** Al cerrar la vN, los criterios de todas las
   versiones anteriores siguen pasando.

| Versión | Qué agrega | Cierre |
| --- | --- | --- |
| **v1** | CRUD (API + pantalla) de las **5 tablas sin FK**: `area_conocimiento`, `termino_clave`, `linea_investigacion`, `programa`, `red` | Criterios en verde + tag `v1` |
| **v2** | CRUD de las **11 tablas con FK** (desplegables desde la API) | Regresión v1 + criterios + tag `v2` |
| **v3** | JWT + sesiones + roles; CRUD de `usuario`/`rol`/`rol_usuario` (solo admin) | Regresión v1–v2 + criterios + tag `v3` |
| **v4** | 10 consultas multitabla, dashboard, manual de marca, responsive y publicación | Regresión total + criterios + tag `v4` |

## 3. Las tres compuertas (no se avanza hasta que estén en verde)

| # | Compuerta | Dónde vive | Pregunta |
| --- | --- | --- | --- |
| 1 | Clarificaciones | Sección 6 de `2_spec.md` | ¿Hay algo que dos del equipo leerían distinto? |
| 2 | Chequeo de constitución | Última sección de `3_plan.md` | ¿El plan respeta cada artículo? |
| 3 | Lista de requisitos | `9_checklist.md` | ¿Cada requisito es medible, único y verificable? |

**La ambigüedad se MARCA, no se rellena:** si algo no está definido se escribe
`[NECESITA ACLARACIÓN: …]` en la spec y se resuelve en la compuerta 1. Si una
IA dice "asumo que…" o "por defecto voy a…", se la detiene: la respuesta va a
la spec, no solo al chat. **Las casillas del checklist las marca una persona**;
una IA puede ayudar a evaluar, pero no puede auto-aprobarse.

## 4. Repositorios y ramas

| Repositorio | Qué es | Regla |
| --- | --- | --- |
| `<equipo>-api` | La API (REST + JSON). Aquí vive `docs/spec_kit/` | NO genera HTML |
| `<equipo>-frontend` | La pantalla; consume la API por HTTP | NO se conecta a la BD |

- Ambos **privados**, con `ccastro2050` invitado desde el primer día
  (*Settings → Collaborators → Add people*).
- **Nadie trabaja en `main`.** Cada integrante tiene su rama (`rama-samuel`, …).
- Todo llega a `main` por **Pull Request**; solo el encargado del `main` hace
  el merge, después de revisar: ¿compila?, ¿cumple la spec?, ¿los criterios
  siguen pasando?
- El cierre de versión es un **tag `vN`** sobre `main`, puesto por el encargado.
- Commits pequeños, frecuentes y en español. "avances" no es un mensaje.
  Cada quien responde en la sustentación por **su** rama y sus commits.

## 5. Secretos

Ningún secreto va escrito en código ni en archivos versionados: la cadena de
conexión, la contraseña de la base, el `JWT_SECRET`.

- El código los lee con `getenv()`; el `docker-compose.yml` los toma de un
  `.env` (`${DB_CLAVE}`).
- El `.env` va en el `.gitignore` desde el primer commit; el repo incluye un
  `.env.example` con valores de mentira.
- Si un secreto se subió por error: **se cambia de inmediato**. Borrarlo del
  último commit no basta, queda en la historia.
- Un secreto quemado en el código anula el criterio de seguridad de la versión.

## 6. Reglas técnicas que aplican a todo el módulo

- PHP 8.3 con `declare(strict_types=1)` en cada archivo; todo en español.
- Tres capas con interfaces: controlador → servicio → repositorio. El
  `ensamblador.php` es el único que hace `new` de clases concretas.
- PDO con *prepared statements*; nada de SQL concatenado.
- **Una ruta específica por tabla** (`/api/programa`); nunca `/api/{tabla}`.
- Borrado **lógico** (`activo = FALSE`), y los listados y desplegables filtran
  los inactivos.
- **Una versión incluye su pantalla.** Si la API responde y la pantalla no, la
  versión no está cerrada.
- El esquema de la base es artefacto dado: los cambios mínimos se numeran y se
  documentan en `5_data_model.md` y en la cabecera de `db/init.sql`.

## 7. Entrega de cada versión

Enlaces a los dos repos (con el tag `vN`) + evidencia del quickstart pasando
(`7_quickstart.md`). Sustentación individual: cada integrante responde por su
rama, y sus commits lo respaldan.
