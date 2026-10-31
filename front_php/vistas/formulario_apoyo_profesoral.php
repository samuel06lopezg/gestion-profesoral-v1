<?php
$registro  = isset($registro) && is_array($registro) ? $registro : [];
$esEdicion = $esEdicion ?? false;
$estudios  = isset($estudios) && is_array($estudios) ? $estudios : [];

$val = function (string $campo) use ($registro): string {
    return (string)($registro[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Apoyo Profesoral' : 'Registrar Nuevo Apoyo Profesoral'; ?></h3>
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

        <form action="<?php echo $esEdicion ? '/apoyos-profesorales/guardar-edicion' : '/apoyos-profesorales/crear'; ?>" method="POST">
            <div class="form-row">
                <!-- Clave foránea Estudios -->
                <div class="form-group col-md-6">
                    <label for="estudios">Estudio Realizado * (Clave foránea)</label>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="estudios" value="<?php echo htmlspecialchars($val('estudios')); ?>">
                        <input type="text" class="form-control" readonly 
                               value="<?php echo htmlspecialchars($registro['estudios_titulo'] ?? ('Estudio #' . $val('estudios'))); ?>">
                    <?php else: ?>
                        <select class="form-control" id="estudios" name="estudios" required>
                            <option value="">— Seleccione un estudio —</option>
                            <?php foreach ($estudios as $e): ?>
                                <option value="<?php echo (int)$e['id']; ?>"
                                    <?php echo (int)$val('estudios') === (int)$e['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['titulo'] . ' (' . $e['universidad'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-6">
                    <label for="con_apoyo">¿Cuenta con Apoyo Institucional? *</label>
                    <select class="form-control" id="con_apoyo" name="con_apoyo" required>
                        <option value="1" <?php echo $val('con_apoyo') === '1' ? 'selected' : ''; ?>>Sí cuenta con apoyo</option>
                        <option value="0" <?php echo $val('con_apoyo') === '0' ? 'selected' : ''; ?>>No cuenta con apoyo</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="institucion">Institución Otorgante * (máximo 45)</label>
                    <input type="text" class="form-control" id="institucion" name="institucion" maxlength="45"
                           value="<?php echo htmlspecialchars($val('institucion')); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="tipo">Tipo de Apoyo * (máximo 45)</label>
                    <input type="text" class="form-control" id="tipo" name="tipo" maxlength="45"
                           placeholder="Ej: Económico, Comisión de estudios, Matrícula"
                           value="<?php echo htmlspecialchars($val('tipo')); ?>" required>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/apoyos-profesorales" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Registrar Apoyo</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
