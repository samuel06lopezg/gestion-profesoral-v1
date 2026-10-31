<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Apoyos Profesorales</h2>
    <a href="/apoyos-profesorales/nuevo" class="btn btn-success">+ Registrar Apoyo Profesoral</a>
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

<?php if (empty($apoyos)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay apoyos profesorales registrados</h4>
        <p class="mb-0">No se encontraron apoyos institucionales activos asociados a estudios realizados.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Estudio Realizado</th>
                            <th>ID Estudio</th>
                            <th>¿Cuenta con apoyo?</th>
                            <th>Institución</th>
                            <th>Tipo de Apoyo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apoyos as $ap): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($ap['estudios_titulo'] ?? ('Estudio #' . $ap['estudios'])); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars((string)$ap['estudios']); ?></td>
                                <td>
                                    <?php if ((int)$ap['con_apoyo'] === 1): ?>
                                        <span class="badge badge-success">Sí cuenta con apoyo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Sin apoyo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ap['institucion']); ?></td>
                                <td><?php echo htmlspecialchars($ap['tipo']); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="/apoyos-profesorales/editar?estudios=<?php echo urlencode((string)$ap['estudios']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/apoyos-profesorales/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este apoyo profesoral?');">
                                        <input type="hidden" name="estudios" value="<?php echo htmlspecialchars((string)$ap['estudios']); ?>">
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
