<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Profesoral — Sistema Académico</title>
    <!-- Carga del archivo CSS local según especificación (Artículo 2) -->
    <link rel="stylesheet" href="/publico/css/bootstrap.min.css">
    <style>
        .dropdown-menu.show {
            display: block;
        }
        .navbar-nav .nav-link {
            padding-right: 0.9rem;
            padding-left: 0.9rem;
        }
        .dropdown-menu {
            max-height: 85vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="/">
            Gestión Profesoral
        </a>
        <button class="navbar-toggler" type="button" id="navbarToggler" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Inicio</a>
                </li>

                <!-- Versión 1: Catálogos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-info font-weight-bold" href="#" id="v1Dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        Versión 1: Catálogos
                    </a>
                    <div class="dropdown-menu" id="v1DropdownMenu" aria-labelledby="v1Dropdown">
                        <h6 class="dropdown-header text-uppercase font-weight-bold text-muted">Parámetros Base</h6>
                        <a class="dropdown-item" href="/programas">Programas Académicos</a>
                        <a class="dropdown-item" href="/redes">Redes Académicas</a>
                        <a class="dropdown-item" href="/lineas">Líneas de Investigación</a>
                        <a class="dropdown-item" href="/terminos">Términos Clave</a>
                        <a class="dropdown-item" href="/areas">Áreas de Conocimiento</a>
                    </div>
                </li>

                <!-- Versión 2: Gestión Docente (11 Tablas Completas) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-warning font-weight-bold" href="#" id="v2Dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        Versión 2: Gestión Docente
                    </a>
                    <div class="dropdown-menu" id="v2DropdownMenu" aria-labelledby="v2Dropdown">
                        <h6 class="dropdown-header text-uppercase font-weight-bold text-primary">Maestro - Detalle Principal</h6>
                        <a class="dropdown-item" href="/docentes"><strong>Docentes (Maestro-Detalle)</strong></a>
                        <a class="dropdown-item" href="/estudios">Estudios Realizados</a>
                        
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header text-uppercase font-weight-bold text-muted">Seguimiento y Calidad</h6>
                        <a class="dropdown-item" href="/evaluaciones">Evaluación Docente</a>
                        <a class="dropdown-item" href="/experiencias">Experiencia Laboral</a>
                        <a class="dropdown-item" href="/reconocimientos">Reconocimiento</a>

                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header text-uppercase font-weight-bold text-muted">Vinculaciones y Beneficios</h6>
                        <a class="dropdown-item" href="/docente-departamentos">Docente - Programa</a>
                        <a class="dropdown-item" href="/intereses-futuros">Intereses de Investigación</a>
                        <a class="dropdown-item" href="/redes-docentes">Redes Académicas Docente</a>
                        <a class="dropdown-item" href="/estudios-areas">Estudios - Áreas</a>
                        <a class="dropdown-item" href="/apoyos-profesorales">Apoyos Profesorales</a>
                        <a class="dropdown-item" href="/becas">Becas de Estudio</a>
                    </div>
                </li>
            </ul>

            <div class="navbar-text text-light small d-none d-md-block">
                <span class="badge badge-info mr-1">V1 Activa</span>
                <span class="badge badge-warning">V2 Completa (11 Tablas)</span>
            </div>
        </div>
    </div>
</nav>

<main class="container flex-grow-1 mb-5">
    <?php echo $contenido ?? ''; ?>
</main>

<footer class="footer py-3 bg-white border-top text-center text-muted mt-auto">
    <div class="container">
        <small>Sistema de Gestión Profesoral y Académica &copy; <?php echo date('Y'); ?> — Proyecto Integrado (V1 y V2 Completa)</small>
    </div>
</footer>

<script>
// Manejo nativo de desplegables sin dependencias externas
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var parent = btn.closest('.dropdown');
            var menu = parent.querySelector('.dropdown-menu');
            var isOpen = menu.classList.contains('show');

            document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('.dropdown.show').forEach(function(p) {
                p.classList.remove('show');
            });

            if (!isOpen) {
                menu.classList.add('show');
                parent.classList.add('show');
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('.dropdown.show').forEach(function(p) {
                p.classList.remove('show');
            });
        }
    });

    var navToggler = document.getElementById('navbarToggler');
    var navMenu = document.getElementById('navbarMenu');
    if (navToggler && navMenu) {
        navToggler.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
});
</script>

</body>
</html>
