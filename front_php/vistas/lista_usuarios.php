<?php
declare(strict_types=1);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-weight-bold text-dark mb-0">Gestión de Usuarios</h2>
        <p class="text-muted mb-0 small">Administración de credenciales y roles asignados (Exclusivo Administrador)</p>
    </div>
    <a href="/usuarios/crear" class="btn btn-primary shadow-sm font-weight-bold">
        + Registrar Usuario
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
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Correo Electrónico</th>
                        <th>Roles Asignados</th>
                        <th>Estado</th>
                        <th class="text-right" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No hay usuarios registrados en el sistema.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="font-weight-bold"><?php echo (int) $u['id']; ?></td>
                                <td>
                                    <code><?php echo htmlspecialchars($u['username']); ?></code>
                                </td>
                                <td><?php echo htmlspecialchars($u['nombre_completo'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if (!empty($u['roles'])): ?>
                                        <?php foreach ($u['roles'] as $r): ?>
                                            <span class="badge badge-info mr-1"><?php echo htmlspecialchars($r); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Sin roles</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)$u['activo'] === 1): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <a href="/usuarios/editar?id=<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form method="POST" action="/usuarios/retirar" class="d-inline" onsubmit="return confirm('¿Seguro que desea retirar este usuario?');">
                                        <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
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
