# Investigación — v2: Maestro-Detalle y Tablas con FK (MariaDB + PHP 8.3)

## 1. Comportamiento de Stored Procedures en MariaDB con PDO

### Delimitadores y Sintaxis
MariaDB utiliza `DELIMITER $$` para definir bloques de procedimientos que contienen punto y coma (`;`).
Soporta `CREATE OR REPLACE PROCEDURE`, lo que garantiza idempotencia al reiniciar o regenerar la base de datos sin necesidad de sentencias `DROP PROCEDURE IF EXISTS` manuales.

### Ausencia de cláusula RETURNING
MariaDB no soporta `RETURNING` en sentencias `INSERT` o `UPDATE`. Para devolver la fila tal como quedó guardada (incluyendo valores por defecto o autoincrementales), el procedimiento debe ejecutar:
```sql
SELECT * FROM tabla WHERE id = p_id;
```
Para tablas con clave primaria `AUTO_INCREMENT`, el ID se captura inmediatamente con `SET v_id = LAST_INSERT_ID();` antes de cualquier otra instrucción `INSERT`.

### Liberación obligatoria de cursores (`closeCursor`)
Al invocar un procedimiento que devuelve conjuntos de resultados (`SELECT`), el driver PDO mantiene abierto el cursor en la conexión. Si se intenta ejecutar otra consulta subsiguiente sin cerrar el cursor, MariaDB responde con el error:
`SQLSTATE[HY000]: General error: 2014 Cannot execute queries while other unbuffered queries are active. Consider using PDOStatement::closeCursor() to free the Mapped result or attribute PDO::MYSQL_ATTR_USE_BUFFERED_QUERY.`
Por consiguiente, es mandatorio invocar:
```php
$sentencia->closeCursor();
```
inmediatamente después de extraer los datos con `fetch()` o `fetchAll()`.

---

## 2. Inspección de Errores de Integridad Referencial en PDO

Cuando MariaDB rechaza una operación de clave foránea o duplicación, el método general `$e->getCode()` retorna genéricamente el SQLSTATE `'23000'`.
Para discernir el motivo real de la falla:
1. `$e->errorInfo[1]` contiene el código de error nativo del motor de MySQL/MariaDB:
   - `1452`: *Cannot add or update a child row: a foreign key constraint fails* (clave foránea apuntando a un registro inexistente).
   - `1062`: *Duplicate entry for key* (violación de clave primaria o única).
   - `1451`: *Cannot delete or update a parent row* (clave foránea restringida).
2. `$e->errorInfo[0]` contiene el SQLSTATE. Para las reglas de negocio personalizadas disparadas mediante:
   ```sql
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mensaje de validación';
   ```
   El SQLSTATE es `'45000'` y el número de error nativo es `1644`. El mensaje en castellano viaja en `$e->errorInfo[2]`.
Ambos casos deben retornar código **HTTP 409 Conflict** al cliente, nunca 500.

---

## 3. Manejo de Claves Foráneas Opcionales en Formularios HTML

En HTML, una opción `<option value="">«(ninguna)»</option>` no seleccionada o seleccionada en blanco viaja en `$_POST` como una cadena de texto vacía `""`, no como valor ausente.
En PHP, un casteo ingenuo como `(int)""` evalúa a `0`. Si se intenta enviar `0` a una columna con FK, la base de datos fallará buscando un registro padre con clave `0` (error 1452).
La conversión canónica obligatoria es:
```php
$lineaInvestigacion = ($_POST['linea_investigacion_principal'] ?? '') === '' ? null : (int)$_POST['linea_investigacion_principal'];
```
Esto asegura que viaja un valor `null` nativo en el JSON y en la sentencia preparada hacia MariaDB.
