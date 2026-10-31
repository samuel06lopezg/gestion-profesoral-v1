<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Evaluaciones Docentes</h2>
    <a href="/evaluaciones/nueva" class="btn btn-success">+ Registrar Nueva Evaluación</a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($evaluaciones)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay evaluaciones registradas</h4>
        <p class="mb-0">No se encontraron evaluaciones docentes activas en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Calificación</th>
                            <th>Semestre</th>
                            <th>Docente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluaciones as $ev): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$ev['id']); ?></strong></td>
                                <td>
                                    <span class="badge badge-primary" style="font-size: 0.95rem;">
                                        <?php echo number_format((float)$ev['calificacion'], 2); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ev['semestre']); ?></td>
                                <td>
                                    <?php
                                    $docenteCedula = (int)$ev['docente'];
                                    echo htmlspecialchars($mapaDocentes[$docenteCedula] ?? ('Docente #' . $docenteCedula));
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="/evaluaciones/editar?id=<?php echo $ev['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/evaluaciones/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar esta evaluación?');">
                                        <input type="hidden" name="id" value="<?php echo $ev['id']; ?>">
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
