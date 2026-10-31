<?php
$registro  = isset($registro) && is_array($registro) ? $registro : [];
$esEdicion = $esEdicion ?? false;
$docentes  = isset($docentes) && is_array($docentes) ? $docentes : [];
$programas = isset($programas) && is_array($programas) ? $programas : [];

$val = function (string $campo) use ($registro): string {
    return (string)($registro[$campo] ?? '');
};
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0"><?php echo $esEdicion ? 'Modificar Vinculación Docente - Programa' : 'Registrar Nueva Vinculación Docente - Programa'; ?></h3>
    </div>
    <div class="card-body">

        <?php if (!empty($errores) && is_array($errores)): ?>
            <div class="alert alert-danger">
                <strong>Atención:</strong> Por favor revise lo siguiente:
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                        <li><?php echo htmlspecialchars((string)$e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $esEdicion ? '/docente-departamentos/guardar-edicion' : '/docente-departamentos/crear'; ?>" method="POST">
            <div class="form-row">
                <!-- Clave foránea Docente -->
                <div class="form-group col-md-6">
                    <label for="docente">Docente * (Clave foránea)</label>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="docente" value="<?php echo htmlspecialchars($val('docente')); ?>">
                        <input type="text" class="form-control" readonly 
                               value="<?php echo htmlspecialchars($registro['docente_nombre'] ?? ('Cédula: ' . $val('docente'))); ?>">
                    <?php else: ?>
                        <select class="form-control" id="docente" name="docente" required>
                            <option value="">— Seleccione un docente —</option>
                            <?php foreach ($docentes as $d): ?>
                                <option value="<?php echo (int)$d['cedula']; ?>"
                                    <?php echo (int)$val('docente') === (int)$d['cedula'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['nombres'] . ' ' . $d['apellidos'] . ' (' . $d['cedula'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Clave foránea Programa / Departamento -->
                <div class="form-group col-md-6">
                    <label for="departamento">Programa Académico / Departamento * (Clave foránea)</label>
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="departamento" value="<?php echo htmlspecialchars($val('departamento')); ?>">
                        <input type="text" class="form-control" readonly 
                               value="<?php echo htmlspecialchars($registro['departamento_nombre'] ?? ('Código: ' . $val('departamento'))); ?>">
                    <?php else: ?>
                        <select class="form-control" id="departamento" name="departamento" required>
                            <option value="">— Seleccione un programa —</option>
                            <?php foreach ($programas as $p): ?>
                                <option value="<?php echo (int)$p['id']; ?>"
                                    <?php echo (int)$val('departamento') === (int)$p['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nombre'] . ' (' . $p['ciudad'] . ' - ' . $p['nivel'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="dedicacion">Dedicación *</label>
                    <select class="form-control" id="dedicacion" name="dedicacion" required>
                        <option value="">— Seleccione dedicación —</option>
                        <option value="Tiempo Completo" <?php echo $val('dedicacion') === 'Tiempo Completo' ? 'selected' : ''; ?>>Tiempo Completo</option>
                        <option value="Medio Tiempo" <?php echo $val('dedicacion') === 'Medio Tiempo' ? 'selected' : ''; ?>>Medio Tiempo</option>
                        <option value="Cátedra" <?php echo $val('dedicacion') === 'Cátedra' ? 'selected' : ''; ?>>Cátedra</option>
                        <option value="Ocasional" <?php echo $val('dedicacion') === 'Ocasional' ? 'selected' : ''; ?>>Ocasional</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="modalidad">Modalidad *</label>
                    <select class="form-control" id="modalidad" name="modalidad" required>
                        <option value="">— Seleccione modalidad —</option>
                        <option value="Presencial" <?php echo $val('modalidad') === 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                        <option value="Virtual" <?php echo $val('modalidad') === 'Virtual' ? 'selected' : ''; ?>>Virtual</option>
                        <option value="A Distancia" <?php echo $val('modalidad') === 'A Distancia' ? 'selected' : ''; ?>>A Distancia</option>
                        <option value="Híbrida" <?php echo $val('modalidad') === 'Híbrida' ? 'selected' : ''; ?>>Híbrida</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_ingreso">Fecha de Ingreso *</label>
                    <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                           value="<?php echo htmlspecialchars($val('fecha_ingreso')); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="fecha_salida">Fecha de Salida (opcional)</label>
                    <input type="date" class="form-control" id="fecha_salida" name="fecha_salida"
                           value="<?php echo htmlspecialchars($val('fecha_salida')); ?>">
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="/docente-departamentos" class="btn btn-secondary">Cancelar</a>
                <div>
                    <?php if ($esEdicion): ?>
                        <button type="submit" name="accion" value="reemplazar" class="btn btn-warning">Guardar la ficha completa</button>
                        <button type="submit" name="accion" value="parcial" class="btn btn-info">Guardar solo lo que cambié</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">Registrar Vinculación</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
