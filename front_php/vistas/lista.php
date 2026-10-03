<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Programas Académicos</h2>
    <a href="/programas/nuevo" class="btn btn-success">+ Registrar Nuevo Programa</a>
</div>

<?php if (isset($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (isset($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($programas)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay programas creados</h4>
        <p class="mb-0">No se encontraron registros de programas académicos activos en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Ciudad</th>
                            <th>Facultad</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programas as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$p['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($p['tipo']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($p['nivel']); ?></span></td>
                                <td><?php echo htmlspecialchars($p['ciudad']); ?></td>
                                <td><?php echo htmlspecialchars((string)$p['facultad']); ?></td>
                                <td class="text-center">
                                    <a href="/programas/editar?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/programas/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este programa?');">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
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