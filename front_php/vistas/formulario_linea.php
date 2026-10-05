<?php
$linea     = isset($linea) && is_array($linea) ? $linea : [];
$esEdicion = $esEdicion ?? false;

$val = function (string $campo) use ($linea): string {
    return (string)($linea[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Línea de Investigación' : 'Registrar Nueva Línea de Investigación'; ?></h3>
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

        <form action="<?php echo $esEdicion ? '/lineas/guardar-edicion' : '/lineas/crear'; ?>" method="POST">

            <?php if ($esEdicion): ?>
                <div class="form-group">
                    <label for="id">Código</label>
                    <input type="number" class="form-control" id="id" name="id"
                           value="<?php echo htmlspecialchars($val('id')); ?>" readonly>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nombre">Nombre de la línea * (máximo 45 caracteres)</label>
                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="45"
                       value="<?php echo htmlspecialchars($val('nombre')); ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción * (máximo 256 caracteres)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" maxlength="256" required><?php echo htmlspecialchars($val('descripcion')); ?></textarea>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/lineas" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Guardar Línea</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>