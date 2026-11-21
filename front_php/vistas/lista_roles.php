<?php
declare(strict_types=1);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-weight-bold text-dark mb-0">Catálogo de Roles</h2>
        <p class="text-muted mb-0 small">Definición de roles del sistema de seguridad (Exclusivo Administrador)</p>
    </div>
    <a href="/roles/crear" class="btn btn-primary shadow-sm font-weight-bold">
        + Registrar Rol
    </a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensajeExito); ?>
    </div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensajeError); ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Rol</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-right" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay roles registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $r): ?>
                            <tr>
                                <td class="font-weight-bold"><?php echo (int) $r['id']; ?></td>
                                <td>
                                    <span class="badge badge-primary font-weight-bold text-uppercase px-2 py-1">
                                        <?php echo htmlspecialchars($r['nombre']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($r['descripcion'] ?? '—'); ?></td>
                                <td>
                                    <?php if ((int)$r['activo'] === 1): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <a href="/roles/editar?id=<?php echo (int) $r['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form method="POST" action="/roles/retirar" class="d-inline" onsubmit="return confirm('¿Seguro que desea retirar este rol?');">
                                        <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Retirar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
