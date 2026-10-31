<?php
$esEdicion = isset($experiencia['id']) && !empty($experiencia['id']);
$tituloVista = $esEdicion ? 'Editar Experiencia Laboral' : 'Registrar Nueva Experiencia Laboral';
$accionUrl = $esEdicion ? '/experiencias/guardar-edicion' : '/experiencias/crear';
$docentes = $docentes ?? [];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0"><?php echo htmlspecialchars($tituloVista); ?></h3>
        <a href="/experiencias" class="btn btn-sm btn-outline-light">Volver al listado</a>
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
                <input type="hidden" name="id" value="<?php echo (int)$experiencia['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="docente">Docente (Clave Foránea) <span class="text-danger">*</span></label>
                    <select class="form-control" id="docente" name="docente" required>
                        <option value="">— Seleccionar Docente —</option>
                        <?php foreach ($docentes as $d): ?>
                            <?php
                            $cedulaDoc = (int)$d['cedula'];
                            $seleccionado = isset($experiencia['docente']) && (int)$experiencia['docente'] === $cedulaDoc;
                            ?>
                            <option value="<?php echo $cedulaDoc; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (C.C. ' . $cedulaDoc . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="nombre_cargo">Cargo Desempeñado <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_cargo" name="nombre_cargo" maxlength="45" required
                           value="<?php echo htmlspecialchars($experiencia['nombre_cargo'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="institucion">Empresa o Institución <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="institucion" name="institucion" maxlength="45" required
                           value="<?php echo htmlspecialchars($experiencia['institucion'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label for="tipo">Tipo <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <?php
                        $tipos = ['Docente', 'Investigador', 'Administrativo', 'Profesional', 'Consultor'];
                        $tipoActual = $experiencia['tipo'] ?? 'Docente';
                        foreach ($tipos as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $tipoActual === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="fecha_inicio">Fecha Inicio <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required
                           value="<?php echo htmlspecialchars($experiencia['fecha_inicio'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="fecha_fin">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                           value="<?php echo htmlspecialchars($experiencia['fecha_fin'] ?? ''); ?>">
                    <small class="form-text text-muted">Dejar vacío si es el cargo actual.</small>
                </div>
            </div>

            <div class="mt-4 border-top pt-3 text-right">
                <a href="/experiencias" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Guardar Experiencia</button>
            </div>
        </form>
    </div>
</div>
