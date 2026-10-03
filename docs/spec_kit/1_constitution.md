# Constitución del proyecto — Módulo de Gestión Profesoral, en PHP

> Las reglas que **no** cambian de una versión a otra. Cuando una decisión de
> versión choca con un artículo de aquí, gana el artículo — o se cambia el
> artículo, a la vista, con su motivo escrito.

Este repositorio construye el módulo de **Gestión Profesoral** del proyecto de
aula, en **PHP puro sobre MariaDB**, con su propia pantalla. La metodología del
curso está en `ProyectosDeAula/docs/0_METODOLOGIA.md` y es obligatoria; esta
constitución la aplica a nuestro módulo.

| | Este proyecto |
| --- | --- |
| Lenguaje | PHP 8.3, sin framework |
| Motor | MariaDB 11 |
| Validación | Escrita a mano |
| Front | PHP + Bootstrap descargado |
| Puertos | front **8112** · API **8113** · MariaDB **13331** |

---

## Artículo 1 — Propósito didáctico ante todo

El código se lee más de lo que se ejecuta. Cada archivo dice en su cabecera qué
hace y por qué está donde está; se prefiere lo explícito a lo ingenioso; los
comentarios explican **decisiones**, no sintaxis obvia (salvo la que sea nueva
para quien lee: promoción de propiedades, `?Tipo`, `??`).

## Artículo 1.1 — Una versión incluye SU FRONT

Cada versión entrega su parte de la API **y** su parte de la pantalla. Una
versión **no está cerrada** si la API responde y la pantalla no.

El front es un **proceso aparte**, en su propio contenedor, que habla con la API
**solo por HTTP**:

1. Su `Dockerfile` **no instala `pdo_mysql`**.
2. Su servicio en el compose **no depende de `mariadb`** ni recibe sus
   credenciales.
3. **Apagando la API, la pantalla sigue en pie**, con su aviso y **sin un solo
   dato**.

Aunque la API y el front estén en carpetas vecinas, **no comparten código**:
ningún `require` del front apunta a `../api_gestion/`. El front trabaja con
arrays, no con las clases de la API.

## Artículo 2 — PHP puro: sin framework y sin Composer

Ni Laravel, ni Symfony, ni Slim, ni `composer.json`. Todo lo que se usa viene
con PHP: `PDO`, `curl`, `json_encode`, `session_start`. Bootstrap se usa
**descargado** en `front_php/publico/`, no por CDN. Una librería solo entra si
se declara aquí, con su razón, y el profesor la aprueba.

## Artículo 3 — Arquitectura de 3 capas estricta

```
index.php (enruta)
   → controladores/   HTTP: valida la forma del cuerpo, traduce a códigos
      → servicios/    las reglas; lanza excepciones de NEGOCIO, no códigos
         → repositorios/   el SQL; PDO con prepared statements
            → MariaDB
```

- El controlador no sabe SQL. El servicio no sabe HTTP. El repositorio no sabe
  de reglas.
- Cada servicio depende de la **interfaz** de su repositorio, nunca de la clase
  concreta.
- `servicios/ensamblador.php` es **el único archivo que hace `new`** de clases
  concretas.
- **Carga manual con `require_once`: las interfaces se cargan ANTES que las
  clases que las implementan.**

## Artículo 4 — Un solo comando para arrancar

`docker compose up -d --build` deja en pie base con datos, API y pantalla. Nada
de "primero corra este script".

## Artículo 5 — El esquema de la base es ARTEFACTO DADO

El script lo entrega el curso (`db_scripts/mysql/gestion_profesoral.sql`). No se
rediseña. Se le aplican los cambios mínimos para que cargue su catálogo, y se
**declaran uno por uno, numerados**, en la cabecera de `db/init.sql` y en
`5_data_model.md`.

## Artículo 6 — El borrado es LÓGICO

`DELETE` marca `activo = FALSE`; la fila se queda. **Todas** las consultas del
repositorio filtran `activo = TRUE`, incluida la del propio `DELETE`. La
pantalla dice **"retirar"**, no "borrar".

## Artículo 7 — Persistencia y recarga

Los datos viven en un volumen de Docker: sobreviven a `docker compose down`;
para volver a cero, `down -v`. Si el código se monta como volumen, guardar un
`.php` basta; si se copia en la imagen (`COPY . .`), **hay que reconstruir**
(`docker compose up -d --build`) para ver el cambio.

## Artículo 8 — Convenciones fijas

| Qué | Convención |
| --- | --- |
| Idioma | Todo en español: clases, métodos, variables, comentarios, mensajes |
| Tipos | `declare(strict_types=1);` como **primera instrucción** de cada archivo |
| Codificación | UTF-8 **sin BOM**, en todos los `.php` |
| Clases | `PascalCase`; métodos y variables `camelCase`; columnas `snake_case` |
| Interfaces | `I` delante: `IServicioPrograma`, `IRepositorioPrograma` |
| JSON | Los nombres de campo son los de las columnas, tal cual |
| Rutas | `kebab-case` en plural/singular fijo por recurso: `/api/area-conocimiento` |

## Artículo 9 — Seguridad y secretos

- **Todo SQL en prepared statements.** Los valores nunca se concatenan.
- Los nombres de columna de un `UPDATE` dinámico salen de una **lista blanca**
  del controlador, jamás del cliente.
- Toda salida a HTML pasa por `htmlspecialchars`.
- **Cero secretos en el código ni en archivos versionados.** La cadena de
  conexión, la clave de la base y el `JWT_SECRET` van por variables de entorno
  (`getenv()`); el compose las toma de un `.env` que está en el `.gitignore`; el
  repo trae `.env.example`. *(A diferencia del ejemplo de clase, que las deja a
  la vista solo por didáctica, este proyecto se publica en la v4.)*

## Artículo 10 — La API es específica, nunca genérica

Una ruta por recurso. **No** existe ni existirá `/api/{tabla}`. Genérico es
barato de escribir y caro de vivir: no puede validar, no puede documentar y
vuelve silencioso cualquier cambio de esquema. Lo mismo en el front:
`listar_programas()`, no `listar($tabla)`.

## Artículo 11 — Git y GitHub

Dos repositorios privados (API y frontend) con `ccastro2050` invitado. Nadie
trabaja en `main`; cada integrante en su rama; todo por Pull Request; solo el
encargado del `main` hace merge; tags `v1…v4`; commits descriptivos en español.

## Artículo 12 — La identidad visual es ARTEFACTO DADO (hasta la v4)

Mientras no se llegue a la v4, la pantalla usa Bootstrap sin colores propios. En
la v4 se adopta el `MANUAL_DE_MARCA.md` del ejemplo o se escribe uno propio; en
ambos casos los valores viven en `marca.css` y los estilos usan la variable,
nunca el valor.
