<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Profesoral - Programas Académicos</title>
    <!-- Carga del archivo CSS local según especificación (Artículo 2) -->
    <link rel="stylesheet" href="/publico/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="/">Gestión Profesoral</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/programas">Programas Académicos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/redes">Redes Académicas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/lineas">Líneas de Investigación</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/terminos">Términos Clave</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container flex-grow-1 mb-5">
    <?php echo $contenido ?? ''; ?>
</main>

<footer class="footer py-3 bg-white border-top text-center text-muted mt-auto">
    <div class="container">
        <small>Sistema de Gestión Profesoral &copy; <?php echo date('Y'); ?> — Módulo Programas</small>
    </div>
</footer>

</body>
</html>
