<?php
declare(strict_types=1);

// Carga manual de dependencias y modelos
require_once __DIR__ . '/excepciones/NoEncontradoExcepcion.php';

require_once __DIR__ . '/modelos/Programa.php';
require_once __DIR__ . '/modelos/AreaConocimiento.php';
require_once __DIR__ . '/modelos/TerminoClave.php';
require_once __DIR__ . '/modelos/LineaInvestigacion.php';
require_once __DIR__ . '/modelos/Red.php';

// Interfaces (van ANTES de quien las usa)
require_once __DIR__ . '/repositorios/IRepositorioPrograma.php';
require_once __DIR__ . '/servicios/IServicioPrograma.php';
require_once __DIR__ . '/repositorios/IRepositorioRed.php';
require_once __DIR__ . '/servicios/IServicioRed.php';
require_once __DIR__ . '/repositorios/IRepositorioLineaInvestigacion.php';
require_once __DIR__ . '/servicios/IServicioLineaInvestigacion.php';
require_once __DIR__ . '/repositorios/IRepositorioTerminoClave.php';
require_once __DIR__ . '/servicios/IServicioTerminoClave.php';

// Repositorios
require_once __DIR__ . '/repositorios/RepositorioProgramaMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioAreaConocimientoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioTerminoClaveMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioLineaInvestigacionMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioRedMariaDB.php';

// Servicios
require_once __DIR__ . '/servicios/ServicioPrograma.php';
require_once __DIR__ . '/servicios/ServicioAreaConocimiento.php';
require_once __DIR__ . '/servicios/ServicioTerminoClave.php';
require_once __DIR__ . '/servicios/ServicioLineaInvestigacion.php';
require_once __DIR__ . '/servicios/ServicioRed.php';
require_once __DIR__ . '/servicios/ensamblador.php';

// Controladores
require_once __DIR__ . '/controladores/ControladorPrograma.php';
require_once __DIR__ . '/controladores/ControladorRed.php';
require_once __DIR__ . '/controladores/ControladorLineaInvestigacion.php';
require_once __DIR__ . '/controladores/ControladorTerminoClave.php';

header('Content-Type: application/json; charset=utf-8');

// Conexión a MariaDB
$host     = getenv('MYSQL_HOST') ?: 'mariadb';
$db       = getenv('MYSQL_DATABASE') ?: 'gestion_profesoral';
$user     = getenv('MYSQL_USER') ?: 'root';
$pass     = getenv('MYSQL_PASSWORD') ?: 'root';
$port     = getenv('MYSQL_PORT') ?: '3306';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $conexion = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_FOUND_ROWS   => true
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'estado'  => 500,
        'mensaje' => 'Error de conexión a la base de datos.',
        'detalle' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Análisis de la ruta HTTP
$metodo = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$partes = explode('/', trim($uri, '/'));

// Diagnóstico: GET /
if ($uri === '/' || $uri === '') {
    http_response_code(200);
    echo json_encode([
        'mensaje'   => 'API de Gestión profesoral funcionando',
        'version'   => 'v1',
        'tablas'    => ['programa', 'area_conocimiento', 'termino_clave', 'linea_investigacion', 'red']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Enrutamiento de Recursos
if (isset($partes[0], $partes[1]) && $partes[0] === 'api') {
    $recurso = $partes[1];
    $limite  = isset($_GET['limite']) ? (int)$_GET['limite'] : 1000;

    switch ($recurso) {
        case 'programa':
            $controlador = new ApiGestion\Controladores\ControladorPrograma(obtenerServicioPrograma($conexion));
            $id = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $id !== null ? $controlador->obtenerUno($id) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $id !== null) {
                // PUT = reemplazar: el cuerpo debe traer TODOS los campos
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($id, $cuerpo);
            } elseif ($metodo === 'PATCH' && $id !== null) {
                // PATCH = actualizar parcial: solo los campos que se quieran cambiar
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($id, $cuerpo);
            } elseif ($metodo === 'DELETE' && $id !== null) {
                $controlador->eliminar($id);
            }
            break;

        case 'area-conocimiento':
            $servicio = obtenerServicioAreaConocimiento($conexion);
            if ($metodo === 'GET') {
                $datos = array_map(fn($a) => $a->toArray(), $servicio->obtenerTodos($limite));
                echo json_encode(['tabla' => 'area_conocimiento', 'limite' => $limite, 'total' => count($datos), 'datos' => $datos], JSON_UNESCAPED_UNICODE);
            }
            break;

                case 'termino-clave':
            $controlador = new ApiGestion\Controladores\ControladorTerminoClave(obtenerServicioTerminoClave($conexion));
            // La llave es TEXTO y llega codificada en la dirección (espacio = %20): se decodifica
            $termino = isset($partes[2]) ? urldecode($partes[2]) : null;

            if ($metodo === 'GET') {
                $termino !== null ? $controlador->obtenerUno($termino) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $termino !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($termino, $cuerpo);
            } elseif ($metodo === 'PATCH' && $termino !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($termino, $cuerpo);
            } elseif ($metodo === 'DELETE' && $termino !== null) {
                $controlador->eliminar($termino);
            }
            break;

        case 'linea-investigacion':
            $controlador = new ApiGestion\Controladores\ControladorLineaInvestigacion(obtenerServicioLineaInvestigacion($conexion));
            $id = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $id !== null ? $controlador->obtenerUno($id) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $id !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($id, $cuerpo);
            } elseif ($metodo === 'PATCH' && $id !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($id, $cuerpo);
            } elseif ($metodo === 'DELETE' && $id !== null) {
                $controlador->eliminar($id);
            }
            break;

        case 'red':
            $controlador = new ApiGestion\Controladores\ControladorRed(obtenerServicioRed($conexion));
            $id = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $id !== null ? $controlador->obtenerUno($id) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $id !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($id, $cuerpo);
            } elseif ($metodo === 'PATCH' && $id !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($id, $cuerpo);
            } elseif ($metodo === 'DELETE' && $id !== null) {
                $controlador->eliminar($id);
            }
            break;


        default:
            http_response_code(404);
            echo json_encode(['estado' => 404, 'mensaje' => 'Recurso no encontrado.']);
            break;
    }
    exit;
}

http_response_code(404);
echo json_encode(['estado' => 404, 'mensaje' => 'Ruta no encontrada.']);