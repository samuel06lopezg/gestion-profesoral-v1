<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Vinculación Docente a Departamentos / Programas</h2>
    <a href="/docente-departamentos/nuevo" class="btn btn-success">+ Nueva Vinculación</a>
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

<?php if (empty($vinculaciones)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay vinculaciones registradas</h4>
        <p class="mb-0">No se encontraron vinculaciones activas entre docentes y programas académicos.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Docente</th>
                            <th>Programa / Departamento</th>
                            <th>Dedicación</th>
                            <th>Modalidad</th>
                            <th>Ingreso</th>
                            <th>Salida</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vinculaciones as $v): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($v['docente_nombre'] ?? ('Cédula: ' . $v['docente'])); ?></strong>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars((string)$v['docente']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($v['departamento_nombre'] ?? ('Código: ' . $v['departamento'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($v['dedicacion']); ?></td>
                                <td><?php echo htmlspecialchars($v['modalidad']); ?></td>
                                <td><?php echo htmlspecialchars($v['fecha_ingreso']); ?></td>
                                <td><?php echo htmlspecialchars($v['fecha_salida'] ?? '—'); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="/docente-departamentos/editar?docente=<?php echo urlencode((string)$v['docente']); ?>&departamento=<?php echo urlencode((string)$v['departamento']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/docente-departamentos/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta vinculación?');">
                                        <input type="hidden" name="docente" value="<?php echo htmlspecialchars((string)$v['docente']); ?>">
                                        <input type="hidden" name="departamento" value="<?php echo htmlspecialchars((string)$v['departamento']); ?>">
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
