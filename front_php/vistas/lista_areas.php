<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Áreas de Conocimiento</h2>
    <a href="/areas/nueva" class="btn btn-success">+ Registrar Nueva Área</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($areas)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay áreas de conocimiento</h4>
        <p class="mb-0">No se encontraron áreas de conocimiento activas.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Gran área</th>
                            <th>Área</th>
                            <th>Disciplina</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($areas as $a): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($a['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($a['gran_area']); ?></td>
                                <td><?php echo htmlspecialchars($a['area']); ?></td>
                                <td><?php echo htmlspecialchars($a['disciplina']); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="/areas/editar?id=<?php echo urlencode($a['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/areas/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta área?');">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
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