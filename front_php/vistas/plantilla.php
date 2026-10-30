<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Profesoral — Sistema Académico</title>
    <!-- Carga del archivo CSS local según especificación (Artículo 2) -->
    <link rel="stylesheet" href="/publico/css/bootstrap.min.css">
    <style>
        /* Mejor soporte visual para dropdowns sin bootstrap.js */
        .dropdown-menu.show {
            display: block;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="/">Gestión Profesoral</a>
        <button class="navbar-toggler" type="button" id="navbarToggler" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav mr-auto">
                <!-- V2: Módulo Maestro-Detalle y Dependientes -->
                <li class="nav-item">
                    <a class="nav-link font-weight-bold text-warning" href="/docentes">Docentes (M-D)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/estudios">Estudios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/evaluaciones">Evaluaciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/experiencias">Experiencia</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/reconocimientos">Reconocimientos</a>
                </li>

                <!-- Separador / V1 -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="v1Dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        Catálogos V1
                    </a>
                    <div class="dropdown-menu" id="v1DropdownMenu" aria-labelledby="v1Dropdown">
                        <a class="dropdown-item" href="/programas">Programas Académicos</a>
                        <a class="dropdown-item" href="/redes">Redes Académicas</a>
                        <a class="dropdown-item" href="/lineas">Líneas de Investigación</a>
                        <a class="dropdown-item" href="/terminos">Términos Clave</a>
                        <a class="dropdown-item" href="/areas">Áreas de Conocimiento</a>
                    </div>
                </li>
            </ul>
            <span class="navbar-text text-light">
                <span class="badge badge-success">V2</span>
            </span>
        </div>
    </div>
</nav>

<main class="container flex-grow-1 mb-5">
    <?php echo $contenido ?? ''; ?>
</main>

<footer class="footer py-3 bg-white border-top text-center text-muted mt-auto">
    <div class="container">
        <small>Sistema de Gestión Profesoral &copy; <?php echo date('Y'); ?> — Módulo Docente (V2)</small>
    </div>
</footer>

<script>
// Manejo nativo de desplegables sin dependencias externas (Bootstrap JS / jQuery)
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
