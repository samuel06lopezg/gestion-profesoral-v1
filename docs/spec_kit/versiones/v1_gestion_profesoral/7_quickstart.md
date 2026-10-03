# Quickstart — v1: los criterios, comprobados a mano

Se corre con Docker Desktop abierto, desde la carpeta del proyecto. Cada paso
nombra el criterio de `2_spec.md` que comprueba.

## Paso 1 — Arrancar (criterio 1)
```
docker compose up -d --build
```
Abrir `http://localhost:8113/` → debe decir `"version":"v1"`.

## Paso 2 — Arranca con datos (criterios 2 y 3)
- `http://localhost:8113/api/programa` → `total: 191`
- `http://localhost:8113/api/area-conocimiento` → `total: 218`
- `http://localhost:8113/api/termino-clave` → **204** (en el navegador, página en blanco)

## Paso 3 — Crear y listar (criterio 4)
Para cada recurso: crear uno desde la pantalla (`http://localhost:8112`) y
confirmar que el listado sube en uno.

## Paso 4 — El ciclo de los cinco verbos (criterio 5)
Con Postman (colección en `postman/`) o desde la pantalla, en cada recurso:
`POST` → `PUT` → `PATCH` de un campo → `GET` → `DELETE` → segundo `DELETE` (404).
Y el contraste: el mismo cuerpo incompleto da 422 por `PUT` y 200 por `PATCH`.

## Paso 5 — El borrado lógico, en la base (criterio 6)
Tras retirar el programa `9001`:
```
docker compose exec mariadb mariadb -uroot -p gestion_profesoral -e "SELECT id, nombre, activo FROM programa WHERE id = 9001;"
```
(la contraseña es la de `DB_CLAVE` en tu `.env`). Debe devolver la fila con
`activo = 0`. Repetir con una fila de cada una de las otras 4 tablas.

## Paso 6 — La validación es la frontera (criterio 7)
`POST /api/programa` sin `nombre` → 422 con `errores`. `POST` con un `id` ya
usado → 500. En ninguno cambia el `total`.

## Paso 7 — Prueba de capas (criterio 8)
```
docker compose exec api_gestion php pruebas/prueba_capas.php
```
Todas las verificaciones pasan. Para demostrar que no usa la base:
`docker compose stop mariadb` y repetir.

## Paso 8 — Secretos (criterio 9)
```
git grep -n -i "password\|clave" -- docker-compose.yml api_gestion
```
No debe aparecer ningún valor escrito; existe `.env.example`; `.env` está en el
`.gitignore`.

## Paso 9 — La pantalla (P1–P9)
1. Recorrer los 5 recursos: lista, nuevo, editar, retirar (P1, P2, P9).
2. Abrir el código fuente de la página: ni `PUT`, ni `PATCH`, ni `/api/` (P4).
3. Llenar un formulario a medias: "ficha completa" lo rechaza, "solo lo que
   cambié" lo guarda; el error no borra lo escrito (P5, P6).
4. `docker compose stop api_gestion` y recargar: la pantalla responde, con su
   aviso y **sin un solo dato** (P7). Volver a encender con
   `docker compose start api_gestion`.
5. Con `termino-clave` vacío: recuadro "todavía no hay" (P8).

## Paso 10 — Cerrar la versión
Con todo en verde y `9_checklist.md` firmado por una persona:
```
git tag v1
git push origin v1
```
(lo hace **solo el encargado del `main`**, sobre `main`).
