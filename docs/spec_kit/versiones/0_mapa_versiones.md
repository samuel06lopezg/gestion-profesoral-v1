# Mapa de versiones — Módulo de Gestión Profesoral

| Versión | Qué agrega (acumulativo) | Estado | Cierre |
| --- | --- | --- | --- |
| **v1** | CRUD (API + pantalla) de las 5 tablas sin FK: `area_conocimiento`, `termino_clave`, `linea_investigacion`, `programa`, `red` | Calificada | Criterios en verde + tag `v1` |
| **v2** | CRUD de tablas con FK (`docente`, `estudios_realizados`, `evaluacion_docente`, `experiecia`, `reconocimiento`, y 6 de vinculación); Maestro-detalle con Stored Procedures; listas desplegables desde la API; 409 por integridad referencial y regla de negocio de retiro | Calificada | Regresión v1 + criterios + tag `v2` |
| **v3** | `POST /api/login` con JWT artesanal (HMAC-SHA256); middleware de autenticación y autorización (guardia 401/403); menú por roles; CRUD de `usuario`, `rol`, `rol_usuario` (solo admin); logout | **En curso** | Regresión v1–v2 + criterios + tag `v3` |
| **v4** | 10 consultas multitabla, dashboard, manual de marca, responsive/PWA, publicación | Pendiente | Regresión total + criterios + tag `v4` |

## Reglas de oro

1. La especificación manda.
2. No se anticipa: nada de una versión futura en la versión en curso.
3. Una versión está terminada solo cuando pasan sus criterios → commit + tag →
   solo entonces se escribe la spec de la siguiente.
4. Una versión cerrada no se reabre: los ajustes van en la siguiente.
5. Regresión obligatoria: al cerrar la vN, pasan los criterios de las anteriores.

## Fechas (las fija el profesor en clase)

| Entrega | Fecha general | Fecha exacta de nuestro grupo |
| --- | --- | --- |
| v1 | última semana de septiembre | `____/____/________` |
| v2 | última semana de octubre | `____/____/________` |
| v3 | segunda semana de noviembre | `21/11/2026` |
| v4 | última semana de noviembre | `____/____/________` |
