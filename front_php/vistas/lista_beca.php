<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Becas de Estudios</h2>
    <a href="/becas/nueva" class="btn btn-success">+ Registrar Beca</a>
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

<?php if (empty($becas)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay becas registradas</h4>
        <p class="mb-0">No se encontraron becas activas asociadas a estudios realizados.</p>
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
                            <th>Tipo de Beca</th>
                            <th>Institución</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($becas as $b): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($b['estudios_titulo'] ?? ('Estudio #' . $b['estudios'])); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars((string)$b['estudios']); ?></td>
                                <td><?php echo htmlspecialchars($b['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($b['institucion']); ?></td>
                                <td><?php echo htmlspecialchars($b['fecha_inicio']); ?></td>
                                <td><?php echo htmlspecialchars($b['fecha_fin'] ?? 'Vigente / Sin fecha'); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="/becas/editar?estudios=<?php echo urlencode((string)$b['estudios']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/becas/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta beca?');">
                                        <input type="hidden" name="estudios" value="<?php echo htmlspecialchars((string)$b['estudios']); ?>">
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
