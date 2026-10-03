# Modelo de datos — v1: tablas sin clave foránea

Base `gestion_profesoral` (MariaDB 11). El script lo entrega el curso y es
**artefacto dado**; aquí se listan las tablas de la v1 y los cambios mínimos
que se le aplican, numerados.

## 1. Las 5 tablas

```
area_conocimiento (id PK, gran_area, area, disciplina, activo)
termino_clave     (termino PK, termino_ingles, activo)
linea_investigacion (id PK AUTO_INCREMENT, nombre, descripcion, activo)
programa          (id PK, nombre, tipo, nivel, fecha_creacion, fecha_cierre?,
                   numero_cohortes, cant_graduados, fecha_actualizacion,
                   ciudad, facultad, activo)
red               (idr PK, nombre, url, pais, activo)
```

Ninguna tiene clave foránea. `programa.facultad` es un entero **sin** FK: la
tabla `facultad` no existe en este módulo.

## 2. Tipos

| Tabla | Columna | Tipo final | Nulo |
| --- | --- | --- | --- |
| `area_conocimiento` | `id` | `VARCHAR(6)` (C1) | no |
| | `gran_area`, `area` | `VARCHAR(60)` | no |
| | `disciplina` | `VARCHAR(150)` (C2) | no |
| `termino_clave` | `termino` | `VARCHAR(30)` | no |
| | `termino_ingles` | `VARCHAR(30)` | **sí** |
| `linea_investigacion` | `id` | `INT AUTO_INCREMENT` | no |
| | `nombre` | `VARCHAR(45)` | no |
| | `descripcion` | `VARCHAR(256)` | no |
| `programa` | `id` | `INT` | no |
| | `nombre` | `VARCHAR(150)` (C3) | no |
| | `tipo`, `nivel`, `fecha_creacion`, `numero_cohortes`, `cant_graduados`, `fecha_actualizacion`, `ciudad` | `VARCHAR(45)` | no |
| | `facultad` | `INT` | no |
| | `fecha_cierre` | `VARCHAR(45)` | **sí** |
| `red` | `idr` | `INT` | no |
| | `nombre`, `pais` | `VARCHAR(45)` | no |
| | `url` | `VARCHAR(255)` (C4) | no |

Todas llevan además `activo BOOLEAN NOT NULL DEFAULT TRUE`.

## 3. Cambios al esquema dado (declarados, numerados)

| # | Cambio | Motivo |
| --- | --- | --- |
| C1 | `area_conocimiento.id`: `INT` → `VARCHAR(6)` | Los códigos del Excel son `1A01`, no enteros |
| C2 | `area_conocimiento.disciplina`: `VARCHAR(60)` → `VARCHAR(150)` | Hay valores más largos que 60 |
| C3 | `programa.nombre`: `VARCHAR(60)` → `VARCHAR(150)` | Evita recortar nombres |
| C4 | `red.url`: `VARCHAR(45)` → `VARCHAR(255)` | Una URL real no cabe en 45 |
| C5 | `activo BOOLEAN NOT NULL DEFAULT TRUE` en las tablas del módulo | El borrado lógico lo exige y el script dado solo lo trae en `rol` y `usuario` |
| C6 | Correcciones de digitación del catálogo al cargarlo | Se anota cada una en la cabecera de `init.sql` |

> Estos cambios **deben coincidir con `db/init.sql`**. Si el script y este
> documento se contradicen, uno de los dos está mal y se corrige antes de cerrar
> la versión.

## 4. Por qué `activo` no está en las fichas

Es una columna de la base, no un campo del recurso. El modelo **no** la
devuelve en `toArray()` y **no** está en la lista blanca del controlador: un
cliente no puede reactivar ni retirar por la puerta de atrás.

## 5. Datos iniciales

| Tabla | Filas al arrancar |
| --- | --- |
| `area_conocimiento` | 218 (Excel de `Mapa_conocimiento/`) |
| `programa` | 191 (Excel de `Mapa_conocimiento/`) |
| `termino_clave`, `linea_investigacion`, `red` | 0 (ver C9 de `2_spec.md`) |
