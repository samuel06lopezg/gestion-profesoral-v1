<?php
$registro  = isset($registro) && is_array($registro) ? $registro : [];
$esEdicion = $esEdicion ?? false;
$redes     = isset($redes) && is_array($redes) ? $redes : [];
$docentes  = isset($docentes) && is_array($docentes) ? $docentes : [];

$val = function (string $campo) use ($registro): string {
    return (string)($registro[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Participación en Red Académica' : 'Registrar Participación en Red Académica'; ?></h3>
    </div>
    <div class="card-body">

        <?php if (!empty($errores) && is_array($errores)): ?>
            <div class="alert alert-danger">
                <strong>Atención:</strong> Por favor revise lo siguiente:
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                        <li><?php echo htmlspecialchars((string)$e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $esEdicion ? '/redes-docentes/guardar-edicion' : '/redes-docentes/crear'; ?>" method="POST">
            <div class="form-row">
                <!-- Clave foránea Red -->
                <div class="form-group col-md-6">
                    <label for="red">Red Académica * (Clave foránea)</label>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="red" value="<?php echo htmlspecialchars($val('red')); ?>">
                        <input type="text" class="form-control" readonly 
                               value="<?php echo htmlspecialchars($registro['red_nombre'] ?? ('ID Red: ' . $val('red'))); ?>">
                    <?php else: ?>
                        <select class="form-control" id="red" name="red" required>
                            <option value="">— Seleccione una red académica —</option>
                            <?php foreach ($redes as $r): ?>
                                <option value="<?php echo (int)$r['idr']; ?>"
                                    <?php echo (int)$val('red') === (int)$r['idr'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r['nombre'] . ' (' . $r['pais'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Clave foránea Docente -->
                <div class="form-group col-md-6">
                    <label for="docente">Docente * (Clave foránea)</label>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="docente" value="<?php echo htmlspecialchars($val('docente')); ?>">
                        <input type="text" class="form-control" readonly 
                               value="<?php echo htmlspecialchars($registro['docente_nombre'] ?? ('Cédula: ' . $val('docente'))); ?>">
                    <?php else: ?>
                        <select class="form-control" id="docente" name="docente" required>
                            <option value="">— Seleccione un docente —</option>
                            <?php foreach ($docentes as $d): ?>
                                <option value="<?php echo (int)$d['cedula']; ?>"
                                    <?php echo (int)$val('docente') === (int)$d['cedula'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (' . $d['cedula'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_inicio">Fecha de Inicio *</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                           value="<?php echo htmlspecialchars($val('fecha_inicio')); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="fecha_fin">Fecha de Fin (opcional / texto)</label>
                    <input type="text" class="form-control" id="fecha_fin" name="fecha_fin" maxlength="45"
                           placeholder="Ej: 2025-12-31 o Vigente"
                           value="<?php echo htmlspecialchars($val('fecha_fin')); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="act_destacadas">Actividades Destacadas *</label>
                <textarea class="form-control" id="act_destacadas" name="act_destacadas" rows="3" required><?php echo htmlspecialchars($val('act_destacadas')); ?></textarea>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/redes-docentes" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Registrar Participación</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
