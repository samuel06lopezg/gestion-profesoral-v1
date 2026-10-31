<?php
$esEdicion = isset($docente['cedula']) && !empty($docente['cedula']) && empty($esNuevo);
$tituloVista = $esEdicion ? 'Editar Docente y Estudios' : 'Registrar Nuevo Docente';
$accionUrl = $esEdicion ? '/docentes/guardar-edicion' : '/docentes/crear';
$lineasInvestigacion = $lineasInvestigacion ?? [];
$estudiosExistentes = $docente['estudios'] ?? [];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0"><?php echo htmlspecialchars($tituloVista); ?></h3>
        <a href="/docentes" class="btn btn-sm btn-outline-light">Volver al listado</a>
    </div>

    <div class="card-body">
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <strong>No se pudo guardar la información:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $accionUrl; ?>" method="POST" id="formDocente">
            <h5 class="text-primary border-bottom pb-2 mb-3">1. Datos Básicos del Docente (Maestro)</h5>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="cedula">Cédula <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cedula" name="cedula"
                           value="<?php echo htmlspecialchars((string)($docente['cedula'] ?? '')); ?>"
                           <?php echo $esEdicion ? 'readonly' : 'required'; ?>>
                    <?php if ($esEdicion): ?>
                        <small class="form-text text-muted">La cédula es la clave primaria y no se puede modificar.</small>
                    <?php endif; ?>
                </div>

                <div class="form-group col-md-4">
                    <label for="nombres">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" maxlength="60" required
                           value="<?php echo htmlspecialchars($docente['nombres'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-5">
                    <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="60" required
                           value="<?php echo htmlspecialchars($docente['apellidos'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="genero">Género <span class="text-danger">*</span></label>
                    <select class="form-control" id="genero" name="genero" required>
                        <option value="">— Seleccionar —</option>
                        <option value="Femenino" <?php echo ($docente['genero'] ?? '') === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                        <option value="Masculino" <?php echo ($docente['genero'] ?? '') === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Otro" <?php echo ($docente['genero'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="fecha_nacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                           value="<?php echo htmlspecialchars($docente['fecha_nacimiento'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="cargo">Cargo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cargo" name="cargo" maxlength="30" required
                           value="<?php echo htmlspecialchars($docente['cargo'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="escalafon">Escalafón <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="escalafon" name="escalafon" maxlength="45" required
                           value="<?php echo htmlspecialchars($docente['escalafon'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="correo" name="correo" maxlength="70" required
                           value="<?php echo htmlspecialchars($docente['correo'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20" required
                           value="<?php echo htmlspecialchars($docente['telefono'] ?? ''); ?>">
                </div>

                <div class="form-group col-md-5">
                    <label for="url_cvlac">URL CvLAC <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="url_cvlac" name="url_cvlac" maxlength="128" required
                           value="<?php echo htmlspecialchars($docente['url_cvlac'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="fecha_actualizacion">Fecha de Actualización <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_actualizacion" name="fecha_actualizacion" required
                           value="<?php echo htmlspecialchars($docente['fecha_actualizacion'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="nacionalidaad">Nacionalidad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nacionalidaad" name="nacionalidaad" maxlength="45" required
                           value="<?php echo htmlspecialchars($docente['nacionalidaad'] ?? 'Colombiana'); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="cat_minciencia">Categoría Minciencias</label>
                    <input type="text" class="form-control" id="cat_minciencia" name="cat_minciencia" maxlength="45"
                           value="<?php echo htmlspecialchars($docente['cat_minciencia'] ?? ''); ?>" placeholder="Opcional">
                </div>

                <div class="form-group col-md-3">
                    <label for="conv_minciencia">Convocatoria Minciencias <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="conv_minciencia" name="conv_minciencia" maxlength="45" required
                           value="<?php echo htmlspecialchars($docente['conv_minciencia'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="linea_investigacion_principal">Línea de Investigación Principal (Clave Foránea)</label>
                    <select class="form-control" id="linea_investigacion_principal" name="linea_investigacion_principal">
                        <option value="">«(ninguna)»</option>
                        <?php foreach ($lineasInvestigacion as $linea): ?>
                            <?php
                            $lineaId = (int)$linea['id'];
                            $seleccionada = isset($docente['linea_investigacion_principal']) && (int)$docente['linea_investigacion_principal'] === $lineaId;
                            ?>
                            <option value="<?php echo $lineaId; ?>" <?php echo $seleccionada ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($linea['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Clave foránea opcional. Seleccionar «(ninguna)» envía null a la base.</small>
                </div>

                <div class="form-group col-md-6">
                    <label for="perfil">Perfil Profesional <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="perfil" name="perfil" rows="2" required><?php echo htmlspecialchars($docente['perfil'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- SECCIÓN MAESTRO-DETALLE: ESTUDIOS REALIZADOS                              -->
            <!-- ========================================================================= -->
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="text-primary mb-0">2. Estudios Realizados (Detalle en el Mismo Envío)</h5>
                        <small class="text-muted">Agregue títulos y estudios para enviarlos atómicamente junto con el docente.</small>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarRenglonEstudio()">
                        + Agregar Renglón de Estudio
                    </button>
                </div>

                <?php if ($esEdicion && !empty($estudiosExistentes)): ?>
                    <div class="mb-3">
                        <label class="font-weight-bold text-secondary">Estudios actualmente registrados para este docente:</label>
                        <table class="table table-sm table-bordered bg-white">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Universidad</th>
                                    <th>Nivel</th>
                                    <th>Fecha</th>
                                    <th>Ciudad / País</th>
                                    <th>Acreditada</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiosExistentes as $est): ?>
                                    <tr>
                                        <td><?php echo $est['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($est['titulo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($est['universidad']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($est['tipo']); ?></span></td>
                                        <td><?php echo htmlspecialchars($est['fecha']); ?></td>
                                        <td><?php echo htmlspecialchars($est['ciudad'] . ', ' . $est['pais']); ?></td>
                                        <td><?php echo (int)$est['ins_acreditada'] === 1 ? 'Sí' : 'No'; ?></td>
                                        <td>
                                            <a href="/estudios/retirar-directo?id=<?php echo $est['id']; ?>&docente=<?php echo $docente['cedula']; ?>"
                                               class="btn btn-xs btn-outline-danger btn-sm"
                                               onclick="return confirm('¿Retirar este estudio?');">Retirar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tablaDetalleEstudios">
                        <thead class="thead-light">
                            <tr>
                                <th>Título <span class="text-danger">*</span></th>
                                <th>Universidad <span class="text-danger">*</span></th>
                                <th>Nivel / Tipo <span class="text-danger">*</span></th>
                                <th>Fecha <span class="text-danger">*</span></th>
                                <th>Ciudad / País <span class="text-danger">*</span></th>
                                <th>Metodología</th>
                                <th>Acreditada</th>
                                <th>Perfil de Egreso</th>
                                <th style="width: 50px;">Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="contenedorRenglones">
                            <!-- Si no es edición y no hay renglones, se agrega un renglón inicial por defecto -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 border-top pt-3 text-right">
                <a href="/docentes" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                    Guardar Docente y Estudios (Envío Único)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function agregarRenglonEstudio(datos = {}) {
    const tbody = document.getElementById('contenedorRenglones');
    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td>
            <input type="text" name="detalle_titulo[]" class="form-control form-control-sm" placeholder="Ej: Ing. Sistemas" required value="${datos.titulo || ''}">
        </td>
        <td>
            <input type="text" name="detalle_universidad[]" class="form-control form-control-sm" placeholder="Ej: Universidad de Antioquia" required value="${datos.universidad || ''}">
        </td>
        <td>
            <select name="detalle_tipo[]" class="form-control form-control-sm" required>
                <option value="Pregrado" ${datos.tipo === 'Pregrado' ? 'selected' : ''}>Pregrado</option>
                <option value="Especialización" ${datos.tipo === 'Especialización' ? 'selected' : ''}>Especialización</option>
                <option value="Maestría" ${datos.tipo === 'Maestría' ? 'selected' : ''}>Maestría</option>
                <option value="Doctorado" ${datos.tipo === 'Doctorado' ? 'selected' : ''}>Doctorado</option>
                <option value="Posdoctorado" ${datos.tipo === 'Posdoctorado' ? 'selected' : ''}>Posdoctorado</option>
            </select>
        </td>
        <td>
            <input type="date" name="detalle_fecha[]" class="form-control form-control-sm" required value="${datos.fecha || '<?php echo date('Y-m-d'); ?>'}">
        </td>
        <td>
            <div class="input-group input-group-sm mb-1">
                <input type="text" name="detalle_ciudad[]" class="form-control" placeholder="Ciudad" required value="${datos.ciudad || ''}">
            </div>
            <div class="input-group input-group-sm">
                <input type="text" name="detalle_pais[]" class="form-control" placeholder="País" required value="${datos.pais || 'Colombia'}">
            </div>
        </td>
        <td>
            <select name="detalle_metodologia[]" class="form-control form-control-sm">
                <option value="Presencial">Presencial</option>
                <option value="Virtual">Virtual</option>
                <option value="A distancia">A distancia</option>
                <option value="Mixta">Mixta</option>
            </select>
        </td>
        <td>
            <select name="detalle_ins_acreditada[]" class="form-control form-control-sm">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </td>
        <td>
            <textarea name="detalle_perfil_egresado[]" class="form-control form-control-sm" rows="2" placeholder="Descripción del perfil...">${datos.perfil_egresado || ''}</textarea>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove();" title="Quitar este renglón">
                &times;
            </button>
        </td>
    `;

    tbody.appendChild(tr);
}

// Al cargar, si es nuevo docente, añadir 1 renglón inicial para facilitar el registro maestro-detalle
<?php if (!$esEdicion): ?>
document.addEventListener('DOMContentLoaded', function() {
    agregarRenglonEstudio();
});
<?php endif; ?>
</script>
