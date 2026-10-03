# Guía de IA — v1: tablas sin clave foránea (PHP + MariaDB)

Cómo construir esta versión con ayuda de una IA, y **qué revisarle**, que es la
mitad que se suele saltar. La IA sigue la spec; no improvisa.

## 1. El andamiaje: carpetas y archivos vacíos

La IA escribe mejor cuando el esqueleto ya existe. En PowerShell, parado en la
carpeta del proyecto:

```
# Carpetas
mkdir docs\spec_kit\versiones\v1_gestion_profesoral, db, postman, pruebas_humo, api_gestion\controladores, api_gestion\excepciones, api_gestion\modelos, api_gestion\pruebas, api_gestion\repositorios, api_gestion\servicios, front_php\vistas, front_php\publico

# Archivos vacíos que la IA llena, uno por uno, y usted revisa
New-Item .gitattributes, .gitignore, .env.example, docker-compose.yml, api_gestion\Dockerfile, api_gestion\index.php, api_gestion\servicios\ensamblador.php, front_php\Dockerfile, front_php\index.php, front_php\cliente_api.php, front_php\vistas\plantilla.php, pruebas_humo\humo_front.py
```

Y por **cada recurso** (`Programa`, `AreaConocimiento`, `TerminoClave`,
`LineaInvestigacion`, `Red`): su modelo, su interfaz y repositorio, su interfaz y
servicio, su controlador y sus dos vistas (lista y formulario).

Dos ausencias a propósito:
- **`db/init.sql` no se pide a la IA.** El esquema es artefacto dado (Art. 5): se
  deriva del script del curso y se le aplican los cambios C1–C6.
- **`front_php/publico/`** lleva Bootstrap descargado una vez. No se genera ni
  va por CDN.

## 2. El prompt

```
Vas a construir la versión 1 de un módulo de un proyecto de aula, en PHP.
Lee primero estos archivos del repositorio y respétalos por encima de lo que
tú creas mejor:

  · docs/spec_kit/1_constitution.md                                  las reglas
  · docs/spec_kit/versiones/v1_gestion_profesoral/2_spec.md           qué construir
  · docs/spec_kit/versiones/v1_gestion_profesoral/5_data_model.md     las tablas
  · docs/spec_kit/versiones/v1_gestion_profesoral/6_contracts.md      cada ruta, exacta
  · docs/0_METODOLOGIA.md                                             cómo se trabaja

REGLA DE ORO: si algo no está definido en esos archivos, NO lo asumas. Escribe
[NECESITA ACLARACIÓN: …] y pregúntame. Si te oyes decir "asumo que…" o "por
defecto voy a…", detente: eso es una ambigüedad de la spec, no un detalle tuyo.

1. PHP 8.3 PURO. Sin framework y SIN COMPOSER. Solo PDO, curl, json_encode,
   session_start. Si te dan ganas de instalar algo, no.

2. TRES CAPAS ESTRICTAS, y cada una ignora a las otras dos:
     controladores/  HTTP: valida la FORMA del cuerpo (422), traduce
                     excepciones a códigos. Cero SQL, cero reglas.
     servicios/      Las reglas. Lanza InvalidArgumentException y
                     NoEncontradoExcepcion. La palabra HTTP no aparece.
     repositorios/   El SQL, en prepared statements de PDO. Nada más.
   Cada servicio recibe la INTERFAZ de su repositorio por constructor.
   El ensamblador es el ÚNICO archivo que hace `new`.

3. CARGA MANUAL CON require_once, EN ESTE ORDEN: excepciones, modelos,
   INTERFACES (repositorios y servicios), repositorios, servicios, ensamblador,
   controladores. Una clase que `implements` una interfaz exige que la
   interfaz ya esté cargada.

4. LOS 5 RECURSOS, cada uno con su ruta, su controlador, su servicio y su
   repositorio (nunca /api/{tabla}):
     /api/area-conocimiento   llave id (texto, hasta 6)
     /api/termino-clave       llave termino (texto)
     /api/linea-investigacion llave id (entero, lo genera la base)
     /api/programa            llave id (entero)
     /api/red                 llave idr (entero)
   Los campos y tamaños están en 2_spec.md §3. No agregues ni quites.

5. EL BORRADO ES LÓGICO: DELETE marca activo = FALSE y la fila se queda.
   TODAS las consultas del repositorio filtran activo = TRUE, incluida la del
   propio DELETE — sin eso, retirar dos veces respondería 200 las dos.
   `activo` NO va en el modelo, ni en toArray(), ni en la lista blanca.

6. Cumple 6_contracts.md AL PIE DE LA LETRA: mismas rutas, códigos y sobre de
   error. Incluido el contraste PUT (falta un campo → 422) contra PATCH (el
   MISMO cuerpo → 200), y el cuerpo vacío del PATCH, que es 400 y no 422.

7. La base YA VIENE DADA en db/init.sql. No escribas SQL de creación de
   tablas ni la modifiques.

8. SECRETOS: ninguna clave escrita en el código ni en docker-compose.yml. Se
   leen con getenv(); el compose las toma de un .env (que va en el .gitignore);
   el repo trae .env.example con valores de mentira.

9. UTF-8 SIN BOM en todos los archivos. declare(strict_types=1); como PRIMERA
   instrucción de cada .php. Todo en español: clases, métodos, variables,
   comentarios y mensajes.

10. Comenta explicando DECISIONES, y también la sintaxis de PHP que un
    estudiante de segundo semestre no ha visto: la promoción de propiedades
    del constructor, `?Tipo`, `??`, las arrow functions.

11. LA VERSIÓN INCLUYE SU PANTALLA. Un FRONT en PHP, en su propio contenedor,
    publicando el puerto 8112:
    · una pantalla por recurso con DIRECCIÓN PROPIA (/programas, /redes…);
    · UNA FUNCIÓN POR OPERACIÓN Y POR RECURSO (listar_programas,
      crear_red…), nunca un cliente genérico con la tabla como parámetro;
    · la pantalla NO habla en jerga: ni PUT, ni PATCH, ni 422, ni /api/. Los
      dos botones de guardar: "Guardar la ficha completa" y "Guardar solo lo
      que cambié";
    · un error de la API NO borra lo que la persona escribió;
    · sin filas, un recuadro "todavía no hay": vacío no es error (la API
      responde 204 y el código no debe reventar);
    · como el borrado es lógico, la palabra es RETIRAR, no borrar;
    · Bootstrap descargado en front_php/publico/, NO por CDN.

    CUATRO COSAS QUE VAS A QUERER HACER Y NO DEBES:
    a) Servir las páginas desde la misma API. NO: son dos procesos.
    b) Un cliente genérico. NO.
    c) Bootstrap por CDN. NO.
    d) Compartir código con la API (require_once '../api_gestion/…'). NO: el
       front trabaja con arrays, no con las clases de la API.

    Con la API apagada, la pantalla debe SEGUIR RESPONDIENDO, con su aviso y
    SIN UN SOLO DATO.

12. NO ANTICIPES. Nada de JWT, roles, dashboard, claves foráneas ni consultas
    de varias tablas: eso es de versiones posteriores.

Trabaja UN recurso a la vez, empezando por `programa`. Termina cada uno con su
prueba antes de pasar al siguiente, y enséñame el diff antes de seguir.
```

## 3. Lo que la IA propone con más naturalidad, y está mal

| Qué | Por qué pasa | Qué revisar |
| --- | --- | --- |
| **Cargar el repositorio antes que su interfaz** | El orden "alfabético" parece natural | En `index.php`, ¿las interfaces van **antes** de las clases? Si no: *Interface … not found* |
| **Archivos con BOM** | Algunos editores de Windows lo agregan al guardar | ¿Algún `.php` empieza con 3 bytes invisibles? Debe ser UTF-8 **sin BOM** |
| **Instalar Composer «solo para el autoload»** | Es lo normal en PHP moderno | ¿Apareció un `composer.json`? |
| **Escribir `root`/`root` en el compose** | "Es solo desarrollo" | ¿Hay una clave en un archivo versionado? Debe venir de `.env` |
| **Un `DELETE FROM`** | Es lo que uno escribe sin pensar | ¿`eliminar()` hace `UPDATE … SET activo = FALSE` y filtra `AND activo = TRUE`? |
| **Olvidar `activo = TRUE` en una consulta** | La primera se escribe bien; la quinta se copia mal | Las consultas de **los 5** repositorios deben filtrarlo |
| **`MYSQL_ATTR_FOUND_ROWS` faltando** | Nadie lo conoce hasta que muerde | Reenvíe un `PUT` con los MISMOS datos: si da 404, falta esa opción |
| **Una API genérica `/api/{tabla}`** | Cinco tablas parecidas piden un molde | ¿Hay un controlador por recurso? |
| **Tratar el 204 como error** | Un 204 no trae cuerpo y el código que espera JSON revienta | Con `termino_clave` vacío, ¿qué muestra la pantalla? |
| **Bootstrap por CDN** | Es lo que hace todo el mundo | ¿`plantilla.php` tiene un `<link>` a un dominio externo? |
| **Llave de texto sin codificar** | `termino` puede llevar espacios o tildes | ¿El front usa `rawurlencode` al armar la ruta? |
| **`activo` en el modelo** | Es una columna, parece un campo más | ¿`toArray()` lo devuelve? No debe |
| **Un `require` del front hacia `../api_gestion/`** | Están ahí al lado y ahorra escribirlos | `git grep "api_gestion" front_php` debe salir vacío |
| **Inventar un dato que la spec no da** | La IA rellena huecos con naturalidad | ¿Dijo "asumo que…"? Se detiene y la respuesta va a la spec |

## 4. Y lo que la IA no va a hacer sola

1. **Marcar el `9_checklist.md`.** Lo marca una persona. Pídale a la IA que
   evalúe la spec con esa lista, pero la firma es suya.
2. **Revisión cruzada.** Antes de repartir el trabajo, cada integrante revisa
   con la lista la parte de la spec que escribió otro.
3. **Sustentar.** En la sustentación individual usted responde por su rama y por
   cada línea que la IA le escribió. Si no la puede explicar, no la entregue.
4. **Hacer los commits por usted.** Commits pequeños, propios y en español: son
   la evidencia de su trabajo.
