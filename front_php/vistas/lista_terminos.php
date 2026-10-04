<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Términos Clave</h2>
    <a href="/terminos/nuevo" class="btn btn-success">+ Registrar Nuevo Término</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($terminos)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay términos clave</h4>
        <p class="mb-0">No se encontraron términos clave activos.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Término</th>
                            <th>En inglés</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terminos as $t): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($t['termino']); ?></strong></td>
                                <td><?php echo htmlspecialchars((string)($t['termino_ingles'] ?? '—')); ?></td>
                                <td class="text-center">
                                    <a href="/terminos/editar?termino=<?php echo urlencode($t['termino']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/terminos/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este término?');">
                                        <input type="hidden" name="termino" value="<?php echo htmlspecialchars($t['termino']); ?>">
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