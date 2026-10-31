<?php
$registro = isset($registro) && is_array($registro) ? $registro : [];
$docentes = isset($docentes) && is_array($docentes) ? $docentes : [];
$terminos = isset($terminos) && is_array($terminos) ? $terminos : [];

$val = function (string $campo) use ($registro): string {
    return (string)($registro[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0">Asignar Interés Futuro de Investigación a Docente</h3>
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

        <form action="/intereses-futuros/crear" method="POST">
            <div class="form-row">
                <!-- Clave foránea Docente -->
                <div class="form-group col-md-6">
                    <label for="docente">Docente * (Clave foránea)</label>
                    <select class="form-control" id="docente" name="docente" required>
                        <option value="">— Seleccione un docente —</option>
                        <?php foreach ($docentes as $d): ?>
                            <option value="<?php echo (int)$d['cedula']; ?>"
                                <?php echo (int)$val('docente') === (int)$d['cedula'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (' . $d['cedula'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Clave foránea Término Clave -->
                <div class="form-group col-md-6">
                    <label for="termino_clave">Término Clave de Investigación * (Clave foránea)</label>
                    <select class="form-control" id="termino_clave" name="termino_clave" required>
                        <option value="">— Seleccione un término clave —</option>
                        <?php foreach ($terminos as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['termino']); ?>"
                                <?php echo $val('termino_clave') === $t['termino'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['termino'] . (!empty($t['termino_ingles']) ? ' (' . $t['termino_ingles'] . ')' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/intereses-futuros" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Asignar Interés</button>
            </div>
        </form>
    </div>
</div>
