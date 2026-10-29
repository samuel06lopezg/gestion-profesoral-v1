<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Reconocimientos</h2>
    <a href="/reconocimientos/nuevo" class="btn btn-success">+ Registrar Nuevo Reconocimiento</a>
</div>

<?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($mensajeExito); ?></div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($mensajeError); ?></div>
<?php endif; ?>

<?php if (empty($reconocimientos)): ?>
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">Todavía no hay reconocimientos registrados</h4>
        <p class="mb-0">No se encontraron reconocimientos activos en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Institución</th>
                            <th>Ámbito</th>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reconocimientos as $rec): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars((string)$rec['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rec['nombre']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($rec['tipo']); ?></span></td>
                                <td><?php echo htmlspecialchars($rec['institucion']); ?></td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($rec['ambito']); ?></span></td>
                                <td><?php echo htmlspecialchars($rec['fecha']); ?></td>
                                <td>
                                    <?php
                                    $docenteCedula = (int)$rec['docente'];
                                    echo htmlspecialchars($mapaDocentes[$docenteCedula] ?? ('Docente #' . $docenteCedula));
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="/reconocimientos/editar?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="/reconocimientos/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea retirar este reconocimiento?');">
                                        <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
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
