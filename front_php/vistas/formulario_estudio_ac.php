<?php
$registro = isset($registro) && is_array($registro) ? $registro : [];
$estudios = isset($estudios) && is_array($estudios) ? $estudios : [];
$areas    = isset($areas) && is_array($areas) ? $areas : [];

$val = function (string $campo) use ($registro): string {
    return (string)($registro[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0">Asociar Área de Conocimiento a Estudio Realizado</h3>
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

        <form action="/estudios-areas/crear" method="POST">
            <div class="form-row">
                <!-- Clave foránea Estudio -->
                <div class="form-group col-md-6">
                    <label for="estudio">Estudio Realizado * (Clave foránea)</label>
                    <select class="form-control" id="estudio" name="estudio" required>
                        <option value="">— Seleccione un estudio —</option>
                        <?php foreach ($estudios as $e): ?>
                            <option value="<?php echo (int)$e['id']; ?>"
                                <?php echo (int)$val('estudio') === (int)$e['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($e['titulo'] . ' (' . $e['universidad'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Clave foránea Área de Conocimiento -->
                <div class="form-group col-md-6">
                    <label for="area_conocimiento">Área de Conocimiento / Disciplina * (Clave foránea)</label>
                    <select class="form-control" id="area_conocimiento" name="area_conocimiento" required>
                        <option value="">— Seleccione un área de conocimiento —</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?php echo htmlspecialchars($a['id']); ?>"
                                <?php echo $val('area_conocimiento') === $a['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['disciplina'] . ' [' . $a['gran_area'] . ' - ' . $a['id'] . ']'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/estudios-areas" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Asociar Área al Estudio</button>
            </div>
        </form>
    </div>
</div>
