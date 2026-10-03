# Plan — v1: tablas sin clave foránea

## 1. Arquitectura

```
front_php (8112)  ──HTTP/JSON──►  api_gestion (8113)  ──PDO──►  mariadb (13331)
   proceso 1                         proceso 2                    proceso 3
```

Tres contenedores. El front **no** conoce la base. Orquesta `docker-compose.yml`.

## 2. Estructura de carpetas

```
api_gestion/
├── index.php                  front controller: ruta → método del controlador
├── controladores/             un Controlador<Recurso>.php por recurso (×5)
├── servicios/                 I<Servicio> + Servicio<Recurso> (×5) + ensamblador.php
├── repositorios/              IRepositorio<Recurso> + Repositorio<Recurso>MariaDB (×5)
├── modelos/                   un modelo por recurso (×5)
├── excepciones/               NoEncontradoExcepcion
└── pruebas/prueba_capas.php   el servicio SIN base de datos

front_php/
├── index.php                  front controller del front
├── cliente_api.php            lo ÚNICO que habla HTTP con la API
├── vistas/                    plantilla + (lista, formulario) por recurso
└── publico/                   Bootstrap descargado

db/init.sql                    las 19 tablas + semillas, con sus cambios numerados
pruebas_humo/humo_front.py     recorrido completo desde la pantalla
.env.example                   las variables de entorno, con valores de mentira
```

## 3. Orden de carga en `api_gestion/index.php`

1. Excepciones → 2. Modelos → 3. **Interfaces** (repositorios y servicios) →
4. Repositorios → 5. Servicios → 6. Ensamblador → 7. Controladores.

> Una clase que `implements` una interfaz exige que la interfaz ya esté
> cargada. Si el `index.php` carga el repositorio antes que su interfaz, PHP
> falla con *Interface … not found*.

## 4. Decisiones de diseño

| Tema | Decisión |
| --- | --- |
| Enrutamiento | Comparaciones legibles en `index.php`; una rama por recurso |
| Validación | Escrita a mano en cada controlador (forma → 422) y en el servicio (reglas → excepciones) |
| PUT vs PATCH | PUT exige todos los campos; PATCH acepta un subconjunto y rechaza `{}` con 400 |
| Llave | `programa.id`, `red.idr`, `area_conocimiento.id` y `termino_clave.termino` las da quien crea; `linea_investigacion.id` la genera la base |
| Borrado | `UPDATE … SET activo = FALSE`; todas las consultas filtran `activo = TRUE` |
| Front | Una función por operación y por recurso (`listar_programas`, `crear_red`, …) |
| Configuración | `getenv()` en un solo sitio; el compose toma los valores de `.env` |
| Codificación | UTF-8 **sin BOM** en todo `.php` |

## 5. Chequeo de constitución (compuerta 2)

| Artículo | ¿El plan lo respeta? | Cómo |
| --- | --- | --- |
| 1.1 Una versión incluye su front | Sí | `front_php/` con pantalla para los 5 recursos |
| 2 PHP puro, sin Composer | Sí | No hay `composer.json`; Bootstrap en `publico/` |
| 3 Tres capas + ensamblador | Sí | Estructura de §2; orden de carga de §3 |
| 4 Un solo comando | Sí | `docker compose up -d --build` |
| 5 Esquema dado | Sí | Cambios C1–C6 numerados en `init.sql` y `5_data_model.md` |
| 6 Borrado lógico | Sí | §4 |
| 7 Persistencia | Sí | Volumen de MariaDB; el código se copia en la imagen → se reconstruye |
| 8 Convenciones | Sí | Español, `strict_types`, UTF-8 sin BOM, `I` en interfaces |
| 9 Seguridad y secretos | **Pendiente de cumplir en el código** | El compose actual escribe `root`/`root`: hay que moverlo a `.env` (tarea T3) |
| 10 API específica | Sí | Una ruta y un controlador por recurso |
| 11 Git y GitHub | Sí | Ver `0_METODOLOGIA.md` §4 |
| 12 Identidad visual | Sí (no aplica hasta la v4) | — |
