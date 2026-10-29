<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Experiencia Laboral</h2>
    <a href="/experiencias/nueva" class="btn btn-success">+ Registrar Nueva Experiencia</a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($experiencias)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay experiencias registradas</h4>
        <p class="mb-0">No se encontraron registros de experiencia laboral activos en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Cargo</th>
                            <th>Institución</th>
                            <th>Tipo</th>
                            <th>Periodo</th>
                            <th>Docente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($experiencias as $exp): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$exp['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($exp['nombre_cargo']); ?></td>
                                <td><?php echo htmlspecialchars($exp['institucion']); ?></td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($exp['tipo']); ?></span></td>
                                <td>
                                    <?php echo htmlspecialchars($exp['fecha_inicio']); ?>
                                    —
                                    <?php echo !empty($exp['fecha_fin']) ? htmlspecialchars($exp['fecha_fin']) : '<span class="text-success font-weight-bold">Actualidad</span>'; ?>
                                </td>
                                <td>
                                    <?php
                                    $docenteCedula = (int)$exp['docente'];
                                    echo htmlspecialchars($mapaDocentes[$docenteCedula] ?? ('Docente #' . $docenteCedula));
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="/experiencias/editar?id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/experiencias/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este registro de experiencia?');">
                                        <input type="hidden" name="id" value="<?php echo $exp['id']; ?>">
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
