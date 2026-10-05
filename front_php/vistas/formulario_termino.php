<?php
$termino   = isset($termino) && is_array($termino) ? $termino : [];
$esEdicion = $esEdicion ?? false;

$val = function (string $campo) use ($termino): string {
    return (string)($termino[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Término Clave' : 'Registrar Nuevo Término Clave'; ?></h3>
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

        <form action="<?php echo $esEdicion ? '/terminos/guardar-edicion' : '/terminos/crear'; ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="termino">Término * (máximo 30 caracteres)</label>
                    <input type="text" class="form-control" id="termino" name="termino" maxlength="30"
                           value="<?php echo htmlspecialchars($val('termino')); ?>"
                           <?php echo $esEdicion ? 'readonly' : 'required'; ?>>
                </div>
                <div class="form-group col-md-6">
                    <label for="termino_ingles">En inglés (opcional, máximo 30 caracteres)</label>
                    <input type="text" class="form-control" id="termino_ingles" name="termino_ingles" maxlength="30"
                           value="<?php echo htmlspecialchars($val('termino_ingles')); ?>">
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/terminos" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Guardar Término</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>