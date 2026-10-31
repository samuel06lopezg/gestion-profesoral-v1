<?php
$esEdicion = isset($reconocimiento['id']) && !empty($reconocimiento['id']);
$tituloVista = $esEdicion ? 'Editar Reconocimiento' : 'Registrar Nuevo Reconocimiento';
$accionUrl = $esEdicion ? '/reconocimientos/guardar-edicion' : '/reconocimientos/crear';
$docentes = $docentes ?? [];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0"><?php echo htmlspecialchars($tituloVista); ?></h3>
        <a href="/reconocimientos" class="btn btn-sm btn-outline-light">Volver al listado</a>
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
                <input type="hidden" name="id" value="<?php echo (int)$reconocimiento['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="docente">Docente Asignado (Clave Foránea) <span class="text-danger">*</span></label>
                    <select class="form-control" id="docente" name="docente" required>
                        <option value="">— Seleccionar Docente —</option>
                        <?php foreach ($docentes as $d): ?>
                            <?php
                            $cedulaDoc = (int)$d['cedula'];
                            $seleccionado = isset($reconocimiento['docente']) && (int)$reconocimiento['docente'] === $cedulaDoc;
                            ?>
                            <option value="<?php echo $cedulaDoc; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (C.C. ' . $cedulaDoc . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="nombre">Nombre de la Distinción o Reconocimiento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="45" required
                           value="<?php echo htmlspecialchars($reconocimiento['nombre'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="tipo">Tipo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tipo" name="tipo" maxlength="45" required
                           value="<?php echo htmlspecialchars($reconocimiento['tipo'] ?? 'Premio a la investigación'); ?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="institucion">Institución Otorgante <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="institucion" name="institucion" maxlength="45" required
                           value="<?php echo htmlspecialchars($reconocimiento['institucion'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label for="ambito">Ámbito <span class="text-danger">*</span></label>
                    <select class="form-control" id="ambito" name="ambito" required>
                        <?php
                        $ambitos = ['Institucional', 'Local', 'Nacional', 'Internacional'];
                        $ambitoActual = $reconocimiento['ambito'] ?? 'Nacional';
                        foreach ($ambitos as $a): ?>
                            <option value="<?php echo $a; ?>" <?php echo $ambitoActual === $a ? 'selected' : ''; ?>><?php echo $a; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label for="fecha">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required
                           value="<?php echo htmlspecialchars($reconocimiento['fecha'] ?? date('Y-m-d')); ?>">
                </div>
            </div>

            <div class="mt-4 border-top pt-3 text-right">
                <a href="/reconocimientos" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Guardar Reconocimiento</button>
            </div>
        </form>
    </div>
</div>
