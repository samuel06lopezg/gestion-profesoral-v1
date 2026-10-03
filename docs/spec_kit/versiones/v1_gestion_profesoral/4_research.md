# Investigación — v1: tablas sin clave foránea

Decisiones que se tomaron con una alternativa sobre la mesa. Cada una queda
escrita con su motivo.

| # | Tema | Se eligió | Se descartó | Por qué |
| --- | --- | --- | --- | --- |
| D1 | Servidor de la API | `php -S` embebido en el contenedor | Apache / nginx + php-fpm | Un proceso, un puerto, cero configuración; es didáctico. Para la v4 se revisa |
| D2 | Carga de clases | `require_once` a mano, interfaces primero | Autoload con Composer | El Art. 2 prohíbe Composer; además el orden de carga es parte de lo que se aprende |
| D3 | Acceso a datos | PDO con prepared statements | `mysqli` | Misma seguridad, interfaz más uniforme |
| D4 | Contraseña de la base | Variable de entorno + `.env` | Escrita en el compose | El Art. 9 lo exige: el proyecto se publica en la v4 |
| D5 | Llave de `termino_clave` | El propio texto del término | Agregar un `id` artificial | El esquema dado la define así y no se rediseña (Art. 5); en la ruta va codificada (`rawurlencode`) |
| D6 | Respuesta de crear | 200 con la ficha | 201 | Igual que el ejemplo de clase; el contrato es el mismo para los 5 recursos |
| D7 | Tabla vacía | 204 sin cuerpo | 200 con `datos: []` | Es lo que hace el ejemplo del curso; la pantalla debe tratar el 204 como "todavía no hay" |
| D8 | Cliente HTTP del front | `curl` de PHP | `file_get_contents` | Permite leer el código de estado y mandar el método HTTP |
| D9 | BOM en archivos `.php` | Prohibido; todo UTF-8 sin BOM | Dejarlo | Con BOM, PHP emite 3 bytes antes de `<?php` y daña `declare(strict_types=1)` y los encabezados |
| D10 | `MYSQL_ATTR_FOUND_ROWS` | Activado en la conexión PDO | Dejar el valor por defecto | Sin él, un `UPDATE` con los **mismos** datos reporta 0 filas y se confunde con "no existe" (404 falso) |
