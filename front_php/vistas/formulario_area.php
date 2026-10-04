<?php
$area      = isset($area) && is_array($area) ? $area : [];
$esEdicion = $esEdicion ?? false;

$val = function (string $campo) use ($area): string {
    return (string)($area[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Área de Conocimiento' : 'Registrar Nueva Área de Conocimiento'; ?></h3>
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

        <form action="<?php echo $esEdicion ? '/areas/guardar-edicion' : '/areas/crear'; ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="id">Código * (máximo 6, como 1A01)</label>
                    <input type="text" class="form-control" id="id" name="id" maxlength="6"
                           value="<?php echo htmlspecialchars($val('id')); ?>"
                           <?php echo $esEdicion ? 'readonly' : 'required'; ?>>
                </div>
                <div class="form-group col-md-4">
                    <label for="gran_area">Gran área * (máximo 60)</label>
                    <input type="text" class="form-control" id="gran_area" name="gran_area" maxlength="60"
                           value="<?php echo htmlspecialchars($val('gran_area')); ?>" required>
                </div>
                <div class="form-group col-md-5">
                    <label for="area">Área * (máximo 60)</label>
                    <input type="text" class="form-control" id="area" name="area" maxlength="60"
                           value="<?php echo htmlspecialchars($val('area')); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="disciplina">Disciplina * (máximo 150)</label>
                <input type="text" class="form-control" id="disciplina" name="disciplina" maxlength="150"
                       value="<?php echo htmlspecialchars($val('disciplina')); ?>" required>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/areas" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Guardar Área</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>