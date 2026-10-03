<?php
// Garantizar que $programa sea un arreglo
$programa  = isset($programa) && is_array($programa) ? $programa : [];
$esEdicion = !empty($programa['id']);
$idValue   = $esEdicion ? $programa['id'] : ($_POST['id'] ?? '');

// Función auxiliar en la vista para leer valores sin advertencias del linter
$val = function(string $campo) use ($programa): string {
    return (string)($_POST[$campo] ?? $programa[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Programa Académico' : 'Registrar Nuevo Programa Académico'; ?></h3>
    </div>
    <div class="card-body">

        <?php if (!empty($errores) && is_array($errores)): ?>
            <div class="alert alert-danger">
                <strong>Atención:</strong> Por favor revise los siguientes inconvenientes:
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                        <li><?php echo htmlspecialchars((string)$e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $esEdicion ? '/programas/guardar-edicion' : '/programas/crear'; ?>" method="POST">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="id">Código / ID *</label>
                    <input type="number" class="form-control" id="id" name="id" value="<?php echo htmlspecialchars((string)$idValue); ?>" <?php echo $esEdicion ? 'readonly' : 'required'; ?>>
                </div>
                <div class="form-group col-md-9">
                    <label for="nombre">Nombre del Programa *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($val('nombre')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="tipo">Tipo *</label>
                    <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo htmlspecialchars($val('tipo')); ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="nivel">Nivel *</label>
                    <input type="text" class="form-control" id="nivel" name="nivel" value="<?php echo htmlspecialchars($val('nivel')); ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="ciudad">Ciudad *</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($val('ciudad')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="fecha_creacion">Fecha Creación *</label>
                    <input type="text" class="form-control" id="fecha_creacion" name="fecha_creacion" value="<?php echo htmlspecialchars($val('fecha_creacion')); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="numero_cohortes">Número Cohortes *</label>
                    <input type="text" class="form-control" id="numero_cohortes" name="numero_cohortes" value="<?php echo htmlspecialchars($val('numero_cohortes')); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="cant_graduados">Cantidad Graduados *</label>
                    <input type="text" class="form-control" id="cant_graduados" name="cant_graduados" value="<?php echo htmlspecialchars($val('cant_graduados')); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="facultad">Facultad (ID) *</label>
                    <input type="number" class="form-control" id="facultad" name="facultad" value="<?php echo htmlspecialchars($val('facultad')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_actualizacion">Fecha Actualización *</label>
                    <input type="text" class="form-control" id="fecha_actualizacion" name="fecha_actualizacion" value="<?php echo htmlspecialchars($val('fecha_actualizacion') ?: date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="fecha_cierre">Fecha Cierre (Opcional)</label>
                    <input type="text" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?php echo htmlspecialchars($val('fecha_cierre')); ?>">
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/programas" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Guardar Programa</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>