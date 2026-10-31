<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Vinculación de Docentes a Redes Académicas</h2>
    <a href="/redes-docentes/nueva" class="btn btn-success">+ Vincular Docente a Red</a>
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
        <h4 class="alert-heading">Todavía no hay redes vinculadas a docentes</h4>
        <p class="mb-0">No se encontraron participaciones de docentes en redes académicas activas.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Red Académica</th>
                            <th>Docente</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Actividades Destacadas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vinculaciones as $v): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($v['red_nombre'] ?? ('ID Red: ' . $v['red'])); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($v['docente_nombre'] ?? ('Cédula: ' . $v['docente'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($v['fecha_inicio']); ?></td>
                                <td><?php echo htmlspecialchars($v['fecha_fin'] ?? 'Vigente'); ?></td>
                                <td><?php echo htmlspecialchars($v['act_destacadas']); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="/redes-docentes/editar?red=<?php echo urlencode((string)$v['red']); ?>&docente=<?php echo urlencode((string)$v['docente']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/redes-docentes/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta participación en la red?');">
                                        <input type="hidden" name="red" value="<?php echo htmlspecialchars((string)$v['red']); ?>">
                                        <input type="hidden" name="docente" value="<?php echo htmlspecialchars((string)$v['docente']); ?>">
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
