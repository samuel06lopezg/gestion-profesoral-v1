<?php
$esEdicion = isset($evaluacion['id']) && !empty($evaluacion['id']);
$tituloVista = $esEdicion ? 'Editar Evaluación Docente' : 'Registrar Nueva Evaluación Docente';
$accionUrl = $esEdicion ? '/evaluaciones/guardar-edicion' : '/evaluaciones/crear';
$docentes = $docentes ?? [];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0"><?php echo htmlspecialchars($tituloVista); ?></h3>
        <a href="/evaluaciones" class="btn btn-sm btn-outline-light">Volver al listado</a>
    </div>

    <div class="card-body">
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <strong>No se pudo guardar la información:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $accionUrl; ?>" method="POST">
            <?php if ($esEdicion): ?>
                <input type="hidden" name="id" value="<?php echo (int)$evaluacion['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="docente">Docente Evaluado (Clave Foránea) <span class="text-danger">*</span></label>
                    <select class="form-control" id="docente" name="docente" required>
                        <option value="">— Seleccionar Docente —</option>
                        <?php foreach ($docentes as $d): ?>
                            <?php
                            $cedulaDoc = (int)$d['cedula'];
                            $seleccionado = isset($evaluacion['docente']) && (int)$evaluacion['docente'] === $cedulaDoc;
                            ?>
                            <option value="<?php echo $cedulaDoc; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (C.C. ' . $cedulaDoc . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">La clave foránea se elige de un desplegable cargado de la API.</small>
                </div>

                <div class="form-group col-md-3">
                    <label for="semestre">Semestre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="semestre" name="semestre" maxlength="45" required
                           value="<?php echo htmlspecialchars($evaluacion['semestre'] ?? '2026-1'); ?>" placeholder="Ej: 2026-1">
                </div>

                <div class="form-group col-md-3">
                    <label for="calificacion">Calificación <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="5" class="form-control" id="calificacion" name="calificacion" required
                           value="<?php echo htmlspecialchars((string)($evaluacion['calificacion'] ?? '4.50')); ?>">
                    <small class="form-text text-muted">Escala de 0.00 a 5.00</small>
                </div>
            </div>

            <div class="mt-4 border-top pt-3 text-right">
                <a href="/evaluaciones" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Guardar Evaluación</button>
            </div>
        </form>
    </div>
</div>
