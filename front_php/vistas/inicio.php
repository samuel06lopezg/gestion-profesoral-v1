<div class="jumbotron bg-white border shadow-sm p-4 p-md-5 mb-4 text-center">
    <h1 class="display-5 font-weight-bold mb-2">Sistema de Gestión Profesoral y Académica</h1>
    <p class="lead text-muted mx-auto" style="max-width: 800px;">
        Portal institucional integrado. Selecciona la versión del sistema a la que deseas acceder para administrar los datos académicos o la planta docente.
    </p>
</div>

<div class="row">
    <!-- Opción 1: Versión 1 -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-info">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                <h4 class="h5 mb-0 font-weight-bold">Versión 1: Catálogos Académicos</h4>
                <span class="badge badge-light text-info font-weight-bold">V1 Base</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Administración de catálogos institucionales, parámetros curriculares, áreas MinCiencias y redes de investigación.
                </p>

                <div class="mb-3">
                    <a href="/programas" class="btn btn-outline-info btn-block btn-lg font-weight-bold">
                        Ingresar a Versión 1 (Catálogos) &raquo;
                    </a>
                </div>

                <div class="list-group list-group-flush mt-auto border-top pt-2">
                    <a href="/programas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Programas Académicos</strong> <small class="text-muted d-block">Pregrados, posgrados y dependencias</small></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/redes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Redes Académicas</strong> <small class="text-muted d-block">Redes y comunidades académicas</small></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/lineas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Líneas de Investigación</strong> <small class="text-muted d-block">Líneas de indagación institucionales</small></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/terminos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Términos Clave</strong> <small class="text-muted d-block">Descriptores temáticos y palabras clave</small></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                    <a href="/areas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Áreas de Conocimiento</strong> <small class="text-muted d-block">Campos del saber y áreas científicas</small></span>
                        <span class="badge badge-secondary">Ver</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Opción 2: Versión 2 -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-warning">
            <div class="card-header bg-dark text-warning d-flex justify-content-between align-items-center py-3">
                <h4 class="h5 mb-0 font-weight-bold">Versión 2: Gestión Docente</h4>
                <span class="badge badge-warning text-dark font-weight-bold">V2 Maestro-Detalle</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Gestión integral de profesores, relación maestro-detalle atómica (Docente con Estudios), evaluaciones, experiencia y reconocimientos.
                </p>

                <div class="mb-3">
                    <a href="/docentes" class="btn btn-warning btn-block btn-lg font-weight-bold text-dark">
                        Ingresar a Versión 2 (Docentes) &raquo;
                    </a>
                </div>

                <div class="list-group list-group-flush mt-auto border-top pt-2">
                    <a href="/docentes" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Docentes (Maestro-Detalle)</strong> <small class="text-muted d-block">Planta docente y estudios en transacción única</small></span>
                        <span class="badge badge-warning text-dark">Maestro</span>
                    </a>
                    <a href="/estudios" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Estudios Realizados</strong> <small class="text-muted d-block">Títulos, universidades y acreditación</small></span>
                        <span class="badge badge-primary">Detalle</span>
                    </a>
                    <a href="/evaluaciones" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Evaluación Docente</strong> <small class="text-muted d-block">Puntajes y periodos evaluativos con FK</small></span>
                        <span class="badge badge-info">CRUD</span>
                    </a>
                    <a href="/experiencias" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Experiencia Laboral</strong> <small class="text-muted d-block">Instituciones y trayectoria con FK</small></span>
                        <span class="badge badge-secondary">CRUD</span>
                    </a>
                    <a href="/reconocimientos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                        <span><strong>Reconocimientos</strong> <small class="text-muted d-block">Premios, distinciones y condecoraciones con FK</small></span>
                        <span class="badge badge-dark">CRUD</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
