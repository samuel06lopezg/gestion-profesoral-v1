<?php
declare(strict_types=1);
$esEdicion = !empty($rol['id']);
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-weight-bold">
                    <?php echo $esEdicion ? 'Editar Rol' : 'Registrar Nuevo Rol'; ?>
                </h5>
                <a href="/roles" class="btn btn-outline-light btn-sm">&larr; Volver</a>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($mensajeError)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> <?php echo htmlspecialchars($mensajeError); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $esEdicion ? '/roles/editar?id=' . (int)$rol['id'] : '/roles/crear'; ?>">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $rol['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="nombre" class="font-weight-bold text-secondary">Nombre del Rol (en minúscula)</label>
                        <input type="text"
                               class="form-control"
                               id="nombre"
                               name="nombre"
                               placeholder="Ej: auditor, decano, etc."
                               value="<?php echo htmlspecialchars($rol['nombre'] ?? ''); ?>"
                               required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="descripcion" class="font-weight-bold text-secondary">Descripción</label>
                        <textarea class="form-control"
                                  id="descripcion"
                                  name="descripcion"
                                  rows="3"><?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/roles" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <?php echo $esEdicion ? 'Guardar Cambios' : 'Crear Rol'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
