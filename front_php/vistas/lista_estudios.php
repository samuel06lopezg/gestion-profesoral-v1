<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Estudios Realizados</h2>
    <a href="/estudios/nuevo" class="btn btn-success">+ Registrar Nuevo Estudio</a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($estudios)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay estudios registrados</h4>
        <p class="mb-0">No se encontraron estudios activos en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Universidad</th>
                            <th>Nivel</th>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th>Ciudad / País</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudios as $e): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$e['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($e['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($e['universidad']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($e['tipo']); ?></span></td>
                                <td><?php echo htmlspecialchars($e['fecha']); ?></td>
                                <td>
                                    <?php
                                    $docenteCedula = (int)$e['docente'];
                                    $nombreDocente = $mapaDocentes[$docenteCedula] ?? ('Docente #' . $docenteCedula);
                                    echo htmlspecialchars($nombreDocente);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($e['ciudad'] . ', ' . $e['pais']); ?></td>
                                <td class="text-center">
                                    <a href="/estudios/editar?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/estudios/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este estudio?');">
                                        <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
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
