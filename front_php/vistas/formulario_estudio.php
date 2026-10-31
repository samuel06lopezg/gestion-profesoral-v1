<?php
$esEdicion = isset($estudio['id']) && !empty($estudio['id']);
$tituloVista = $esEdicion ? 'Editar Estudio Realizado' : 'Registrar Nuevo Estudio Realizado';
$accionUrl = $esEdicion ? '/estudios/guardar-edicion' : '/estudios/crear';
$docentes = $docentes ?? [];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0"><?php echo htmlspecialchars($tituloVista); ?></h3>
        <a href="/estudios" class="btn btn-sm btn-outline-light">Volver al listado</a>
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
                <input type="hidden" name="id" value="<?php echo (int)$estudio['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="docente">Docente Asignado (Clave Foránea) <span class="text-danger">*</span></label>
                    <select class="form-control" id="docente" name="docente" required>
                        <option value="">— Seleccionar Docente —</option>
                        <?php foreach ($docentes as $d): ?>
                            <?php
                            $cedulaDoc = (int)$d['cedula'];
                            $seleccionado = isset($estudio['docente']) && (int)$estudio['docente'] === $cedulaDoc;
                            ?>
                            <option value="<?php echo $cedulaDoc; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (C.C. ' . $cedulaDoc . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">La clave foránea se selecciona de la lista desplegable cargada desde la API.</small>
                </div>

                <div class="form-group col-md-6">
                    <label for="titulo">Título Obtenido <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" maxlength="45" required
                           value="<?php echo htmlspecialchars($estudio['titulo'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="universidad">Universidad o Institución <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="universidad" name="universidad" maxlength="50" required
                           value="<?php echo htmlspecialchars($estudio['universidad'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="tipo">Nivel / Tipo de Estudio <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <?php
                        $tipos = ['Pregrado', 'Especialización', 'Maestría', 'Doctorado', 'Posdoctorado'];
                        $tipoActual = $estudio['tipo'] ?? 'Pregrado';
                        foreach ($tipos as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $tipoActual === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="fecha">Fecha de Grado <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required
                           value="<?php echo htmlspecialchars($estudio['fecha'] ?? date('Y-m-d')); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="ciudad">Ciudad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" maxlength="45" required
                           value="<?php echo htmlspecialchars($estudio['ciudad'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="pais">País <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pais" name="pais" maxlength="45" required
                           value="<?php echo htmlspecialchars($estudio['pais'] ?? 'Colombia'); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label for="metodologia">Metodología <span class="text-danger">*</span></label>
                    <select class="form-control" id="metodologia" name="metodologia" required>
                        <?php
                        $metodos = ['Presencial', 'Virtual', 'A distancia', 'Mixta'];
                        $metodoActual = $estudio['metodologia'] ?? 'Presencial';
                        foreach ($metodos as $m): ?>
                            <option value="<?php echo $m; ?>" <?php echo $metodoActual === $m ? 'selected' : ''; ?>><?php echo $m; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label for="ins_acreditada">Acreditada <span class="text-danger">*</span></label>
                    <select class="form-control" id="ins_acreditada" name="ins_acreditada" required>
                        <option value="1" <?php echo (int)($estudio['ins_acreditada'] ?? 1) === 1 ? 'selected' : ''; ?>>Sí</option>
                        <option value="0" <?php echo (int)($estudio['ins_acreditada'] ?? 1) === 0 ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="perfil_egresado">Perfil de Egreso</label>
                <textarea class="form-control" id="perfil_egresado" name="perfil_egresado" rows="3"><?php echo htmlspecialchars($estudio['perfil_egresado'] ?? ''); ?></textarea>
            </div>

            <div class="mt-4 border-top pt-3 text-right">
                <a href="/estudios" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Guardar Estudio</button>
            </div>
        </form>
    </div>
</div>
