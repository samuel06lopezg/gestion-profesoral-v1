<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Redes Académicas</h2>
    <a href="/redes/nueva" class="btn btn-success">+ Registrar Nueva Red</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($redes)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay redes registradas</h4>
        <p class="mb-0">No se encontraron redes académicas activas.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Dirección web</th>
                            <th>País</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($redes as $r): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$r['idr']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($r['url']); ?></td>
                                <td><?php echo htmlspecialchars($r['pais']); ?></td>
                                <td class="text-center">
                                    <a href="/redes/editar?idr=<?php echo $r['idr']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/redes/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta red?');">
                                        <input type="hidden" name="idr" value="<?php echo $r['idr']; ?>">
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