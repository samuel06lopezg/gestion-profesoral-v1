<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Líneas de Investigación</h2>
    <a href="/lineas/nueva" class="btn btn-success">+ Registrar Nueva Línea</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($lineas)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay líneas de investigación</h4>
        <p class="mb-0">No se encontraron líneas de investigación activas.</p>
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
                            <th>Descripción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineas as $l): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$l['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($l['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($l['descripcion']); ?></td>
                                <td class="text-center">
                                    <a href="/lineas/editar?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/lineas/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta línea?');">
                                        <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
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