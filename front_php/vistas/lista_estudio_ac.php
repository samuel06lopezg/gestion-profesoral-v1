<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Asociación de Estudios a Áreas de Conocimiento</h2>
    <a href="/estudios-areas/nuevo" class="btn btn-success">+ Asociar Estudio a Área</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensajeExito); ?>
    </div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensajeError); ?>
    </div>
<?php endif; ?>

<?php if (empty($asociaciones)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay áreas asociadas a estudios</h4>
        <p class="mb-0">No se encontraron asociaciones activas entre estudios realizados y áreas de conocimiento.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Estudio Realizado</th>
                            <th>ID Estudio</th>
                            <th>Área de Conocimiento / Disciplina</th>
                            <th>Código Área</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asociaciones as $a): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($a['estudio_titulo'] ?? ('Estudio #' . $a['estudio'])); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars((string)$a['estudio']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($a['area_disciplina'] ?? ('Área ' . $a['area_conocimiento'])); ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($a['area_conocimiento']); ?></span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <form action="/estudios-areas/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta asociación?');">
                                        <input type="hidden" name="estudio" value="<?php echo htmlspecialchars((string)$a['estudio']); ?>">
                                        <input type="hidden" name="area_conocimiento" value="<?php echo htmlspecialchars((string)$a['area_conocimiento']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Retirar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
