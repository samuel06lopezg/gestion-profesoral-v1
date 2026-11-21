<?php
// Rol de solo lectura (consulta): se ocultan los controles de escritura.
// La API sigue siendo quien rechaza con 403; esto solo evita mostrar botones inútiles.
$__roles = array_map('strtolower', (array)($_SESSION['roles'] ?? []));
$soloLectura = !empty($_SESSION['token']) && !in_array('admin', $__roles, true) && !in_array('docente', $__roles, true);
?>
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
            padding-right: 0.8rem;
            padding-left: 0.8rem;
        }
        .dropdown-menu {
            max-height: 85vh;
            overflow-y: auto;
        }
<?php if ($soloLectura): ?>
        a[href$="/nuevo"], a[href$="/nueva"], a[href$="/crear"], a[href*="/editar"],
        form[action$="/retirar"], a[href*="retirar-directo"] { display: none !important; }
<?php endif; ?>

        /* Mejoras estéticas globales para botones, tablas y badges */
        .table td, .table th {
            vertical-align: middle !important;
        }
        .table th {
            font-size: 0.88rem;
            letter-spacing: 0.3px;
        }
        .table td.text-center, .table th.text-center {
            white-space: nowrap !important;
            width: 1% !important;
        }
        .table td.text-center .btn,
        .table td.text-center form {
            display: inline-block !important;
            margin: 1px 3px !important;
            vertical-align: middle;
        }
        .btn {
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.15s ease-in-out;
        }
        .btn-sm {
            padding: 0.3rem 0.65rem;
            font-size: 0.84rem;
        }
        .btn-success {
            background-color: #198754;
            border-color: #198754;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
            box-shadow: 0 2px 5px rgba(0,0,0,0.18);
        }
        .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
            background-color: #f8faff;
        }
        .btn-outline-primary:hover {
            color: #ffffff;
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(13,110,253,0.25);
        }
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        .btn-outline-danger:hover {
            color: #ffffff;
            background-color: #dc3545;
            border-color: #dc3545;
            box-shadow: 0 2px 4px rgba(220,53,69,0.25);
        }
        .badge {
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.4em 0.65em;
            border-radius: 4px;
        }
        .card {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php
$usuarioSesion = $_SESSION['usuario'] ?? null;
$rolesSesion = (array)($_SESSION['roles'] ?? []);
$esAdmin = in_array('admin', array_map('strtolower', $rolesSesion), true);
?>

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
                        Catálogos (v1)
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
                        Gestión Docente (v2)
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

                <!-- Versión 3: Seguridad y Roles (Solo visible para rol Admin) -->
                <?php if ($esAdmin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-success font-weight-bold" href="#" id="v3Dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        Seguridad y Roles (v3)
                    </a>
                    <div class="dropdown-menu" id="v3DropdownMenu" aria-labelledby="v3Dropdown">
                        <h6 class="dropdown-header text-uppercase font-weight-bold text-success">Control de Acceso (Admin)</h6>
                        <a class="dropdown-item" href="/usuarios"><strong>Usuarios del Sistema</strong></a>
                        <a class="dropdown-item" href="/roles">Catálogo de Roles</a>
                    </div>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Sección de usuario en sesión y control de acceso -->
            <ul class="navbar-nav ml-auto align-items-center">
                <?php if ($usuarioSesion !== null): ?>
                    <li class="nav-item text-light mr-3 small">
                        <span class="text-muted">Conectado:</span>
                        <strong class="text-white"><?php echo htmlspecialchars($usuarioSesion['username'] ?? 'Usuario'); ?></strong>
                        <?php foreach ($rolesSesion as $rolNombre): ?>
                            <span class="badge badge-primary ml-1"><?php echo htmlspecialchars($rolNombre); ?></span>
                        <?php endforeach; ?>
                    </li>
                    <li class="nav-item">
                        <a href="/salir" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/entrar" class="btn btn-success btn-sm font-weight-bold">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container flex-grow-1 mb-5">
    <?php echo $contenido ?? ''; ?>
</main>

<footer class="footer py-3 bg-white border-top text-center text-muted mt-auto">
    <div class="container">
        <small>Sistema de Gestión Profesoral y Académica &copy; <?php echo date('Y'); ?> — v3: Autenticación, JWT y Control de Acceso por Roles</small>
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
