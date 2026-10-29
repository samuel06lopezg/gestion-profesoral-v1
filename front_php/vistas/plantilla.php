<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Profesoral — Sistema Académico</title>
    <!-- Carga del archivo CSS local según especificación (Artículo 2) -->
    <link rel="stylesheet" href="/publico/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="/">Gestión Profesoral</a>
        <div class="collapse navbar-collapse">
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
                    <a class="nav-link dropdown-toggle" href="#" id="v1Dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Catálogos V1
                    </a>
                    <div class="dropdown-menu" aria-labelledby="v1Dropdown">
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

</body>
</html>
