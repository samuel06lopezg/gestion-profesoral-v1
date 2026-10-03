# Tareas — v1: tablas sin clave foránea

Cada tarea cita el requisito o criterio que cumple. Se reparten por rama; cada
una termina en un Pull Request. `[ ]` = pendiente, `[x]` = hecha (la marca una
persona).

## A. Base y configuración
- [ ] **T1** `db/init.sql`: aplicar C1–C4 y cargar las semillas (218 áreas, 191
  programas), con la cabecera de cambios numerados. *(criterio 2)*
- [ ] **T2** Corregir los errores de digitación del catálogo y anotarlos (C6).
- [ ] **T3** Sacar la clave de la base del `docker-compose.yml`: variables
  `${DB_USUARIO}`/`${DB_CLAVE}` desde `.env`; crear `.env.example`; `.env` en el
  `.gitignore`. *(criterio 9, Art. 9)*
- [ ] **T4** Llenar `.gitignore` y `.gitattributes` (hoy están vacíos).
- [ ] **T5** Quitar el BOM de todos los `.php` y dejar la regla escrita.

## B. API
- [ ] **T6** `index.php`: cargar las **interfaces antes** que sus clases
  (Servicio y Repositorio de cada recurso).
- [ ] **T7** Controlador completo (RF1–RF6) para `programa` — ya existe; revisar
  contra `6_contracts.md`.
- [ ] **T8** Controlador completo para `area_conocimiento`.
- [ ] **T9** Controlador completo para `termino_clave` (llave de texto).
- [ ] **T10** Controlador completo para `linea_investigacion` (llave generada).
- [ ] **T11** Controlador completo para `red`.
- [ ] **T12** Interfaz + servicio para los 4 recursos que hoy solo tienen
  repositorio.
- [ ] **T13** Repositorios: las consultas filtran `activo = TRUE`, **incluida la
  del DELETE**; conexión PDO con `MYSQL_ATTR_FOUND_ROWS`.
- [ ] **T14** `pruebas/prueba_capas.php` cubre los 5 servicios con repositorios
  falsos. *(criterio 8)*

## C. Pantalla
- [ ] **T15** `cliente_api.php`: una función por operación y por recurso.
- [ ] **T16** Vistas (lista, formulario) y rutas propias para los 4 recursos
  restantes; `programa` ya existe.
- [ ] **T17** Bootstrap descargado en `front_php/publico/` (sin CDN).
- [ ] **T18** Aviso de "servicio no disponible" con la API apagada. *(P7)*
- [ ] **T19** `pruebas_humo/humo_front.py` (hoy está vacío): recorrido completo
  desde la pantalla. *(P1–P9)*

## D. Documentación y cierre
- [ ] **T20** Colección de Postman en `postman/`, en el orden del quickstart.
- [ ] **T21** Revisión cruzada del `9_checklist.md` (cada quien revisa la parte
  que escribió otro).
- [ ] **T22** Quickstart pasado completo; evidencia guardada.
- [ ] **T23** Pull Requests aprobados, merge a `main`, tag `v1` (encargado).
