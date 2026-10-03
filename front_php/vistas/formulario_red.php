<?php
$red       = isset($red) && is_array($red) ? $red : [];
$esEdicion = $esEdicion ?? false;

// Lee un valor de la red sin avisos si no existe
$val = function (string $campo) use ($red): string {
    return (string)($red[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Red Académica' : 'Registrar Nueva Red Académica'; ?></h3>
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

        <form action="<?php echo $esEdicion ? '/redes/guardar-edicion' : '/redes/crear'; ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="idr">Código *</label>
                    <input type="number" class="form-control" id="idr" name="idr"
                           value="<?php echo htmlspecialchars($val('idr')); ?>"
                           <?php echo $esEdicion ? 'readonly' : 'required'; ?>>
                </div>
                <div class="form-group col-md-9">
                    <label for="nombre">Nombre de la Red *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre"
                           value="<?php echo htmlspecialchars($val('nombre')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="url">Dirección web *</label>
                    <input type="text" class="form-control" id="url" name="url"
                           value="<?php echo htmlspecialchars($val('url')); ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="pais">País *</label>
                    <input type="text" class="form-control" id="pais" name="pais"
                           value="<?php echo htmlspecialchars($val('pais')); ?>" required>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/redes" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Guardar Red</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>