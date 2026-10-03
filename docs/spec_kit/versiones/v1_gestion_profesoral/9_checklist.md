# Lista de requisitos — v1 (compuerta 3)

> **Las casillas las marca una persona.** Una IA puede ayudar a evaluar, pero no
> puede auto-aprobarse. **No se escribe código con esta lista en rojo.**

Cada requisito debe ser **medible, único y verificable**. Si una casilla no se
puede marcar, se vuelve a la spec.

## Calidad de la especificación (`2_spec.md`)
- [ ] Cada RF dice un código HTTP exacto para cada desenlace.
- [ ] Ningún requisito usa palabras vagas ("rápido", "amigable", "adecuado").
- [ ] Cada criterio de aceptación se comprueba con un comando o una acción
  concreta, y dice qué resultado se espera.
- [ ] Las 5 fichas listan todos sus campos con tipo, tamaño y obligatoriedad.
- [ ] No quedan `[NECESITA ACLARACIÓN]` sin resolver (hoy: **C9**).
- [ ] Las clarificaciones C1–C9 fueron confirmadas por el equipo o el profesor.
- [ ] Lo que la versión **no** incluye está escrito.

## Coherencia entre documentos
- [ ] Los cambios C1–C6 de `5_data_model.md` coinciden con `db/init.sql`.
- [ ] Las rutas de `6_contracts.md` coinciden con las de `index.php`.
- [ ] Los pasos de `7_quickstart.md` cubren cada criterio de `2_spec.md`.
- [ ] Cada tarea de `8_tasks.md` cita un requisito o criterio.
- [ ] El chequeo de constitución de `3_plan.md` está hecho, artículo por
  artículo, y no tiene un "pendiente" sin tarea asociada.

## Metodología
- [ ] Los dos repositorios son privados y `ccastro2050` es colaborador.
- [ ] Nadie ha hecho commit directo a `main`.
- [ ] Cada integrante tiene su rama y commits propios y descriptivos.
- [ ] No hay un solo secreto en el historial de git.

## Cierre
- [ ] Todos los criterios de `2_spec.md` pasaron (con evidencia).
- [ ] Tag `v1` puesto sobre `main` por el encargado.

Revisado por: ______________________   Fecha: ____/____/________
