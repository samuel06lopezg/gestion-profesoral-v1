<?php
declare(strict_types=1);

// Carga manual de dependencias y modelos
require_once __DIR__ . '/excepciones/NoEncontradoExcepcion.php';
require_once __DIR__ . '/excepciones/ConflictoIntegridadExcepcion.php';

// Modelos v1
require_once __DIR__ . '/modelos/Programa.php';
require_once __DIR__ . '/modelos/AreaConocimiento.php';
require_once __DIR__ . '/modelos/TerminoClave.php';
require_once __DIR__ . '/modelos/LineaInvestigacion.php';
require_once __DIR__ . '/modelos/Red.php';

// Modelos v2
require_once __DIR__ . '/modelos/EstudiosRealizados.php';
require_once __DIR__ . '/modelos/Docente.php';
require_once __DIR__ . '/modelos/EvaluacionDocente.php';
require_once __DIR__ . '/modelos/Experiencia.php';
require_once __DIR__ . '/modelos/Reconocimiento.php';

// Interfaces Repositorios y Servicios (van ANTES de quien las usa)
require_once __DIR__ . '/repositorios/IRepositorioPrograma.php';
require_once __DIR__ . '/servicios/IServicioPrograma.php';
require_once __DIR__ . '/repositorios/IRepositorioRed.php';
require_once __DIR__ . '/servicios/IServicioRed.php';
require_once __DIR__ . '/repositorios/IRepositorioLineaInvestigacion.php';
require_once __DIR__ . '/servicios/IServicioLineaInvestigacion.php';
require_once __DIR__ . '/repositorios/IRepositorioTerminoClave.php';
require_once __DIR__ . '/servicios/IServicioTerminoClave.php';
require_once __DIR__ . '/repositorios/IRepositorioAreaConocimiento.php';
require_once __DIR__ . '/servicios/IServicioAreaConocimiento.php';

// Interfaces v2
require_once __DIR__ . '/repositorios/IRepositorioDocente.php';
require_once __DIR__ . '/servicios/IServicioDocente.php';
require_once __DIR__ . '/repositorios/IRepositorioEstudiosRealizados.php';
require_once __DIR__ . '/servicios/IServicioEstudiosRealizados.php';
require_once __DIR__ . '/repositorios/IRepositorioEvaluacionDocente.php';
require_once __DIR__ . '/servicios/IServicioEvaluacionDocente.php';
require_once __DIR__ . '/repositorios/IRepositorioExperiencia.php';
require_once __DIR__ . '/servicios/IServicioExperiencia.php';
require_once __DIR__ . '/repositorios/IRepositorioReconocimiento.php';
require_once __DIR__ . '/servicios/IServicioReconocimiento.php';

// Repositorios v1
require_once __DIR__ . '/repositorios/errores_de_integridad.php';
require_once __DIR__ . '/repositorios/RepositorioProgramaMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioAreaConocimientoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioTerminoClaveMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioLineaInvestigacionMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioRedMariaDB.php';

// Repositorios v2
require_once __DIR__ . '/repositorios/RepositorioDocenteMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioEstudiosRealizadosMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioEvaluacionDocenteMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioExperienciaMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioReconocimientoMariaDB.php';

// Servicios v1
require_once __DIR__ . '/servicios/ServicioPrograma.php';
require_once __DIR__ . '/servicios/ServicioAreaConocimiento.php';
require_once __DIR__ . '/servicios/ServicioTerminoClave.php';
require_once __DIR__ . '/servicios/ServicioLineaInvestigacion.php';
require_once __DIR__ . '/servicios/ServicioRed.php';

// Servicios v2
require_once __DIR__ . '/servicios/ServicioDocente.php';
require_once __DIR__ . '/servicios/ServicioEstudiosRealizados.php';
require_once __DIR__ . '/servicios/ServicioEvaluacionDocente.php';
require_once __DIR__ . '/servicios/ServicioExperiencia.php';
require_once __DIR__ . '/servicios/ServicioReconocimiento.php';
require_once __DIR__ . '/servicios/ensamblador.php';

// Controladores v1
require_once __DIR__ . '/controladores/ControladorPrograma.php';
require_once __DIR__ . '/controladores/ControladorRed.php';
require_once __DIR__ . '/controladores/ControladorLineaInvestigacion.php';
require_once __DIR__ . '/controladores/ControladorTerminoClave.php';
require_once __DIR__ . '/controladores/ControladorAreaConocimiento.php';

// Controladores v2
require_once __DIR__ . '/controladores/ControladorDocente.php';
require_once __DIR__ . '/controladores/ControladorEstudiosRealizados.php';
require_once __DIR__ . '/controladores/ControladorEvaluacionDocente.php';
require_once __DIR__ . '/controladores/ControladorExperiencia.php';
require_once __DIR__ . '/controladores/ControladorReconocimiento.php';

header('Content-Type: application/json; charset=utf-8');

// Conexión a MariaDB
$host     = getenv('MYSQL_HOST') ?: 'mariadb';
$db       = getenv('MYSQL_DATABASE') ?: 'gestion_profesoral';
$user     = getenv('MYSQL_USER') ?: 'root';
$pass     = getenv('MYSQL_PASSWORD') ?: '';
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
        'mensaje' => 'API de Gestión Profesoral funcionando',
        'version' => 'v2',
        'tablas'  => [
            'programa', 'area_conocimiento', 'termino_clave', 'linea_investigacion', 'red',
            'docente', 'estudios_realizados', 'evaluacion_docente', 'experiecia', 'reconocimiento'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Enrutamiento de Recursos
if (isset($partes[0], $partes[1]) && $partes[0] === 'api') {
    $recurso = $partes[1];

    switch ($recurso) {
        // --- RECURSOS V1 ---
        case 'programa':
            $controlador = new ApiGestion\Controladores\ControladorPrograma(obtenerServicioPrograma($conexion));
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

        case 'area-conocimiento':
            $controlador = new ApiGestion\Controladores\ControladorAreaConocimiento(obtenerServicioAreaConocimiento($conexion));
            $id = isset($partes[2]) ? urldecode($partes[2]) : null;

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

        case 'termino-clave':
            $controlador = new ApiGestion\Controladores\ControladorTerminoClave(obtenerServicioTerminoClave($conexion));
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

        // --- RECURSOS V2 ---
        case 'docente':
            $controlador = new ApiGestion\Controladores\ControladorDocente(obtenerServicioDocente($conexion));
            $cedula = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $cedula !== null ? $controlador->obtenerUno($cedula) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $cedula !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($cedula, $cuerpo);
            } elseif ($metodo === 'PATCH' && $cedula !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($cedula, $cuerpo);
            } elseif ($metodo === 'DELETE' && $cedula !== null) {
                $controlador->eliminar($cedula);
            }
            break;

        case 'estudios-realizados':
            $controlador = new ApiGestion\Controladores\ControladorEstudiosRealizados(obtenerServicioEstudiosRealizados($conexion));
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

        case 'evaluacion-docente':
            $controlador = new ApiGestion\Controladores\ControladorEvaluacionDocente(obtenerServicioEvaluacionDocente($conexion));
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

        case 'experiencia':
            $controlador = new ApiGestion\Controladores\ControladorExperiencia(obtenerServicioExperiencia($conexion));
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

        case 'reconocimiento':
            $controlador = new ApiGestion\Controladores\ControladorReconocimiento(obtenerServicioReconocimiento($conexion));
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
            echo json_encode(['estado' => 404, 'mensaje' => 'Recurso no encontrado.'], JSON_UNESCAPED_UNICODE);
            break;
    }
    exit;
}

http_response_code(404);
echo json_encode(['estado' => 404, 'mensaje' => 'Ruta no encontrada.'], JSON_UNESCAPED_UNICODE);
