<?php
declare(strict_types=1);
$usuarioSesion = $_SESSION['usuario'] ?? null;
$rolesSesion = (array)($_SESSION['roles'] ?? []);
$esAdmin = in_array('admin', array_map('strtolower', $rolesSesion), true);
?>

<div class="jumbotron bg-white border shadow-sm p-4 p-md-5 mb-4 text-center">
    <h1 class="display-5 font-weight-bold mb-2">Sistema de Gestión Profesoral y Académica</h1>
    <p class="lead text-muted mx-auto" style="max-width: 850px;">
        Portal institucional integrado. Administra los catálogos base (v1), la planta docente completa con relaciones maestro-detalle (v2) y el control de acceso con autenticación JWT y roles (v3).
    </p>

    <?php if ($usuarioSesion !== null): ?>
        <hr class="my-3">
        <div class="d-inline-block text-left bg-light p-3 rounded border">
            <span class="text-muted">Sesión Activa:</span>
            <strong class="text-dark"><?php echo htmlspecialchars($usuarioSesion['nombre_completo'] ?? $usuarioSesion['username']); ?></strong>
            <span class="text-muted small ml-1">(<?php echo htmlspecialchars($usuarioSesion['username']); ?>)</span>
            <div class="mt-1">
                <span class="text-muted small">Roles asignados:</span>
                <?php foreach ($rolesSesion as $r): ?>
                    <span class="badge badge-success text-uppercase"><?php echo htmlspecialchars($r); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Opción 1: Versión 1 -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-info">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-weight-bold">Versión 1: Catálogos</h5>
                <span class="badge badge-light text-info font-weight-bold">v1 Base</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted small">
                    Catálogos institucionales, parámetros curriculares, áreas MinCiencias y redes sin claves foráneas.
                </p>

                <div class="list-group list-group-flush mt-auto border-top pt-2">
                    <a href="/programas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Programas Académicos</strong></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/redes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Redes Académicas</strong></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/lineas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Líneas de Investigación</strong></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/terminos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Términos Clave</strong></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/areas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Áreas de Conocimiento</strong></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Opción 2: Versión 2 -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-warning">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-weight-bold">Versión 2: Gestión Docente</h5>
                <span class="badge badge-dark font-weight-bold">v2 11 Tablas</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted small">
                    Relaciones maestro-detalle y tablas dependientes con integridad referencial e inserción transaccional.
                </p>

                <div class="list-group list-group-flush mt-auto border-top pt-2" style="max-height: 290px; overflow-y: auto;">
                    <a href="/docentes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span><strong>Docentes (Maestro-Detalle)</strong></span>
                        <span class="badge badge-warning">Ver</span>
                    </a>
                    <a href="/estudios" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Estudios Realizados</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/evaluaciones" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Evaluación Docente</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/experiencias" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Experiencia Laboral</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/reconocimientos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Reconocimientos</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/docente-departamentos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Docente - Programa</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/intereses-futuros" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Intereses Investigación</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/redes-docentes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Redes Docente</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/estudios-areas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Estudios - Áreas</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/apoyos-profesorales" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Apoyos Profesorales</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/becas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-1">
                        <span>Becas de Estudio</span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Opción 3: Versión 3 -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-weight-bold">Versión 3: Seguridad y Roles</h5>
                <span class="badge badge-light text-success font-weight-bold">v3 RBAC</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted small">
                    Autenticación por JSON Web Token (JWT) artesanal sin dependencias, encriptación BCRYPT y control de permisos por roles.
                </p>

                <div class="list-group list-group-flush mt-auto border-top pt-2">
                    <?php if ($esAdmin): ?>
                        <a href="/usuarios" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                            <span><strong>Usuarios del Sistema</strong> <small class="text-muted d-block">CRUD de credenciales y roles</small></span>
                            <span class="badge badge-success">Admin</span>
                        </a>
                        <a href="/roles" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                            <span><strong>Catálogo de Roles</strong> <small class="text-muted d-block">admin, docente, consulta</small></span>
                            <span class="badge badge-success">Admin</span>
                        </a>
                    <?php else: ?>
                        <div class="list-group-item px-2 text-muted small">
                            <span class="font-weight-bold">Nivel de Acceso Actual:</span>
                            <div class="mt-1">
                                <?php if (in_array('docente', array_map('strtolower', $rolesSesion), true)): ?>
                                    <span class="badge badge-warning text-uppercase">Docente</span> — Permisos de lectura y escritura en módulos académicos.
                                <?php elseif (in_array('consulta', array_map('strtolower', $rolesSesion), true)): ?>
                                    <span class="badge badge-secondary text-uppercase">Consulta</span> — Solo lectura (operaciones de escritura bloqueadas).
                                <?php else: ?>
                                    <span class="badge badge-light text-uppercase">Invitado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
