<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Docentes</h2>
    <a href="/docentes/nuevo" class="btn btn-success">+ Registrar Nuevo Docente</a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($docentes)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay docentes registrados</h4>
        <p class="mb-0">No se encontraron docentes activos en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres y Apellidos</th>
                            <th>Cargo</th>
                            <th>Escalafón</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Minciencias</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($docentes as $d): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$d['cedula']); ?></strong></td>
                                <td><?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($d['cargo']); ?></td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($d['escalafon']); ?></span></td>
                                <td><?php echo htmlspecialchars($d['correo']); ?></td>
                                <td><?php echo htmlspecialchars($d['telefono']); ?></td>
                                <td>
                                    <small>
                                        <?php echo htmlspecialchars($d['cat_minciencia'] ?? 'Sin categorizar'); ?><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($d['conv_minciencia']); ?></span>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="/docentes/editar?cedula=<?php echo $d['cedula']; ?>" class="btn btn-sm btn-outline-primary">Editar / Detalle</a>
                                    <form action="/docentes/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este docente?');">
                                        <input type="hidden" name="cedula" value="<?php echo $d['cedula']; ?>">
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
