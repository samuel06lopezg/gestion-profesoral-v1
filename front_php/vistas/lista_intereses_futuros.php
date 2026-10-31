<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Intereses Futuros de Investigación</h2>
    <a href="/intereses-futuros/nuevo" class="btn btn-success">+ Asignar Interés a Docente</a>
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

<?php if (empty($intereses)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay intereses de investigación asignados</h4>
        <p class="mb-0">No se encontraron vinculaciones entre docentes y términos clave de investigación.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Docente</th>
                            <th>Cédula</th>
                            <th>Término Clave de Interés</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($intereses as $i): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($i['docente_nombre'] ?? 'Docente'); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars((string)$i['docente']); ?></td>
                                <td>
                                    <span class="badge badge-primary px-2 py-1" style="font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($i['termino_clave']); ?>
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <form action="/intereses-futuros/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este interés de investigación?');">
                                        <input type="hidden" name="docente" value="<?php echo htmlspecialchars((string)$i['docente']); ?>">
                                        <input type="hidden" name="termino_clave" value="<?php echo htmlspecialchars((string)$i['termino_clave']); ?>">
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
