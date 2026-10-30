<div class="jumbotron bg-white border shadow-sm p-4 p-md-5 mb-4">
    <h1 class="display-5 font-weight-bold">Módulo de Gestión Profesoral</h1>
    <p class="lead text-muted">Sistema de administración académica integral (Versión 2). Desde este panel puedes gestionar la planta docente y consultar todos los catálogos institucionales.</p>
    <hr class="my-4">
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-warning font-weight-bold mr-2 mb-2" href="/docentes" role="button">
            Gestionar Docentes (Maestro-Detalle) &raquo;
        </a>
        <a class="btn btn-primary mr-2 mb-2" href="/estudios" role="button">
            Estudios Realizados &raquo;
        </a>
        <a class="btn btn-info text-white mr-2 mb-2" href="/evaluaciones" role="button">
            Evaluaciones &raquo;
        </a>
        <a class="btn btn-secondary mr-2 mb-2" href="/experiencias" role="button">
            Experiencia &raquo;
        </a>
        <a class="btn btn-dark mb-2" href="/reconocimientos" role="button">
            Reconocimientos &raquo;
        </a>
    </div>
</div>

<div class="row">
    <!-- Bloque V2 -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-primary">
            <div class="card-header bg-primary text-white font-weight-bold">
                Módulos Asignados (Versión 2)
            </div>
            <div class="card-body">
                <p class="text-muted small">Gestión maestro-detalle con procedimientos almacenados, integridad referencial y selects para claves foráneas.</p>
                <div class="list-group list-group-flush">
                    <a href="/docentes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Docentes</strong>
                            <div class="small text-muted">Maestro principal con regla de no retiro si tiene estudios</div>
                        </div>
                        <span class="badge badge-warning badge-pill">Maestro</span>
                    </a>
                    <a href="/estudios" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Estudios Realizados</strong>
                            <div class="small text-muted">Detalle con selección dinámica de docente</div>
                        </div>
                        <span class="badge badge-primary badge-pill">Detalle</span>
                    </a>
                    <a href="/evaluaciones" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Evaluación Docente</strong>
                            <div class="small text-muted">Registro de valoraciones académicas con FK docente</div>
                        </div>
                        <span class="badge badge-info badge-pill">CRUD</span>
                    </a>
                    <a href="/experiencias" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Experiencia</strong>
                            <div class="small text-muted">Trayectoria profesional vinculada a docente</div>
                        </div>
                        <span class="badge badge-secondary badge-pill">CRUD</span>
                    </a>
                    <a href="/reconocimientos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Reconocimiento</strong>
                            <div class="small text-muted">Distinciones y premios de los docentes</div>
                        </div>
                        <span class="badge badge-dark badge-pill">CRUD</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bloque V1 -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-secondary">
            <div class="card-header bg-secondary text-white font-weight-bold">
                Catálogos Base (Versión 1)
            </div>
            <div class="card-body">
                <p class="text-muted small">Catálogos institucionales base mantenidos para compatibilidad y regresión del sistema.</p>
                <div class="list-group list-group-flush">
                    <a href="/programas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Programas Académicos</strong>
                            <div class="small text-muted">Pregrados, posgrados y dependencias</div>
                        </div>
                        <span class="badge badge-light badge-pill">V1</span>
                    </a>
                    <a href="/redes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Redes Académicas</strong>
                            <div class="small text-muted">Redes de investigación y colaboración</div>
                        </div>
                        <span class="badge badge-light badge-pill">V1</span>
                    </a>
                    <a href="/lineas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Líneas de Investigación</strong>
                            <div class="small text-muted">Líneas institucionales activas</div>
                        </div>
                        <span class="badge badge-light badge-pill">V1</span>
                    </a>
                    <a href="/terminos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Términos Clave</strong>
                            <div class="small text-muted">Vocabulario y descriptores temáticos</div>
                        </div>
                        <span class="badge badge-light badge-pill">V1</span>
                    </a>
                    <a href="/areas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Áreas de Conocimiento</strong>
                            <div class="small text-muted">Clasificación temática MinCiencias</div>
                        </div>
                        <span class="badge badge-light badge-pill">V1</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
