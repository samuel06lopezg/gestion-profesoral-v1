<?php
declare(strict_types=1);
$esEdicion = !empty($usuario['id']);
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-weight-bold">
                    <?php echo $esEdicion ? 'Editar Usuario' : 'Registrar Nuevo Usuario'; ?>
                </h5>
                <a href="/usuarios" class="btn btn-outline-light btn-sm">&larr; Volver</a>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($mensajeError)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> <?php echo htmlspecialchars($mensajeError); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $esEdicion ? '/usuarios/editar?id=' . (int)$usuario['id'] : '/usuarios/crear'; ?>">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $usuario['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="username" class="font-weight-bold text-secondary">Nombre de Usuario (Login)</label>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               value="<?php echo htmlspecialchars($usuario['username'] ?? ''); ?>"
                               required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="font-weight-bold text-secondary">Correo Electrónico Institucional</label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>"
                               required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nombre_completo" class="font-weight-bold text-secondary">Nombre Completo</label>
                        <input type="text"
                               class="form-control"
                               id="nombre_completo"
                               name="nombre_completo"
                               value="<?php echo htmlspecialchars($usuario['nombre_completo'] ?? ''); ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary d-block">Roles asignados</label>
                        <?php
                        $rolesDisponibles = $rolesDisponibles ?? [];
                        $nombresMarcados  = array_map('strtolower', (array)($usuario['roles'] ?? []));
                        $idsMarcados      = array_map('intval', (array)($_POST['rol_ids'] ?? []));
                        ?>
                        <?php foreach ($rolesDisponibles as $rolOpcion): ?>
                            <?php $marcado = in_array(strtolower((string)$rolOpcion['nombre']), $nombresMarcados, true)
                                || in_array((int)$rolOpcion['id'], $idsMarcados, true); ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="rol_ids[]"
                                       id="rol_<?php echo (int)$rolOpcion['id']; ?>"
                                       value="<?php echo (int)$rolOpcion['id']; ?>"
                                       <?php echo $marcado ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="rol_<?php echo (int)$rolOpcion['id']; ?>">
                                    <?php echo htmlspecialchars((string)$rolOpcion['nombre']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <small class="form-text text-muted">Sin roles el usuario puede iniciar sesión pero no escribir datos.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold text-secondary">
                            Contraseña <?php echo $esEdicion ? '<small class="text-muted">(deje en blanco si no desea cambiarla)</small>' : ''; ?>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               <?php echo $esEdicion ? '' : 'required'; ?>>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <?php echo $esEdicion ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
