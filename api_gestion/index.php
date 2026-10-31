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

// Modelos v2 (Docente y Dependientes)
require_once __DIR__ . '/modelos/EstudiosRealizados.php';
require_once __DIR__ . '/modelos/Docente.php';
require_once __DIR__ . '/modelos/EvaluacionDocente.php';
require_once __DIR__ . '/modelos/Experiencia.php';
require_once __DIR__ . '/modelos/Reconocimiento.php';

// Modelos v2 (Vinculaciones y Beneficios)
require_once __DIR__ . '/modelos/DocenteDepartamento.php';
require_once __DIR__ . '/modelos/InteresFuturo.php';
require_once __DIR__ . '/modelos/RedDocente.php';
require_once __DIR__ . '/modelos/EstudioAreaConocimiento.php';
require_once __DIR__ . '/modelos/ApoyoProfesoral.php';
require_once __DIR__ . '/modelos/Beca.php';

// Interfaces v1
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

// Interfaces v2 (Docente y Dependientes)
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

// Interfaces v2 (Vinculaciones y Beneficios)
require_once __DIR__ . '/repositorios/IRepositorioDocenteDepartamento.php';
require_once __DIR__ . '/servicios/IServicioDocenteDepartamento.php';
require_once __DIR__ . '/repositorios/IRepositorioInteresFuturo.php';
require_once __DIR__ . '/servicios/IServicioInteresFuturo.php';
require_once __DIR__ . '/repositorios/IRepositorioRedDocente.php';
require_once __DIR__ . '/servicios/IServicioRedDocente.php';
require_once __DIR__ . '/repositorios/IRepositorioEstudioAreaConocimiento.php';
require_once __DIR__ . '/servicios/IServicioEstudioAreaConocimiento.php';
require_once __DIR__ . '/repositorios/IRepositorioApoyoProfesoral.php';
require_once __DIR__ . '/servicios/IServicioApoyoProfesoral.php';
require_once __DIR__ . '/repositorios/IRepositorioBeca.php';
require_once __DIR__ . '/servicios/IServicioBeca.php';

// Repositorios y utilidades
require_once __DIR__ . '/repositorios/errores_de_integridad.php';
require_once __DIR__ . '/repositorios/ManejadorIntegridad.php';
require_once __DIR__ . '/repositorios/RepositorioProgramaMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioAreaConocimientoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioTerminoClaveMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioLineaInvestigacionMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioRedMariaDB.php';

require_once __DIR__ . '/repositorios/RepositorioDocenteMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioEstudiosRealizadosMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioEvaluacionDocenteMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioExperienciaMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioReconocimientoMariaDB.php';

require_once __DIR__ . '/repositorios/RepositorioDocenteDepartamentoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioInteresFuturoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioRedDocenteMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioEstudioAreaConocimientoMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioApoyoProfesoralMariaDB.php';
require_once __DIR__ . '/repositorios/RepositorioBecaMariaDB.php';

// Servicios
require_once __DIR__ . '/servicios/ServicioPrograma.php';
require_once __DIR__ . '/servicios/ServicioAreaConocimiento.php';
require_once __DIR__ . '/servicios/ServicioTerminoClave.php';
require_once __DIR__ . '/servicios/ServicioLineaInvestigacion.php';
require_once __DIR__ . '/servicios/ServicioRed.php';

require_once __DIR__ . '/servicios/ServicioDocente.php';
require_once __DIR__ . '/servicios/ServicioEstudiosRealizados.php';
require_once __DIR__ . '/servicios/ServicioEvaluacionDocente.php';
require_once __DIR__ . '/servicios/ServicioExperiencia.php';
require_once __DIR__ . '/servicios/ServicioReconocimiento.php';

require_once __DIR__ . '/servicios/ServicioDocenteDepartamento.php';
require_once __DIR__ . '/servicios/ServicioInteresFuturo.php';
require_once __DIR__ . '/servicios/ServicioRedDocente.php';
require_once __DIR__ . '/servicios/ServicioEstudioAreaConocimiento.php';
require_once __DIR__ . '/servicios/ServicioApoyoProfesoral.php';
require_once __DIR__ . '/servicios/ServicioBeca.php';
require_once __DIR__ . '/servicios/ensamblador.php';

// Controladores
require_once __DIR__ . '/controladores/ControladorPrograma.php';
require_once __DIR__ . '/controladores/ControladorRed.php';
require_once __DIR__ . '/controladores/ControladorLineaInvestigacion.php';
require_once __DIR__ . '/controladores/ControladorTerminoClave.php';
require_once __DIR__ . '/controladores/ControladorAreaConocimiento.php';

require_once __DIR__ . '/controladores/ControladorDocente.php';
require_once __DIR__ . '/controladores/ControladorEstudiosRealizados.php';
require_once __DIR__ . '/controladores/ControladorEvaluacionDocente.php';
require_once __DIR__ . '/controladores/ControladorExperiencia.php';
require_once __DIR__ . '/controladores/ControladorReconocimiento.php';

require_once __DIR__ . '/controladores/ControladorDocenteDepartamento.php';
require_once __DIR__ . '/controladores/ControladorInteresFuturo.php';
require_once __DIR__ . '/controladores/ControladorRedDocente.php';
require_once __DIR__ . '/controladores/ControladorEstudioAreaConocimiento.php';
require_once __DIR__ . '/controladores/ControladorApoyoProfesoral.php';
require_once __DIR__ . '/controladores/ControladorBeca.php';

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
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'estado'  => 500,
        'mensaje' => 'Error de conexión con la base de datos.',
        'detalle' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    http_response_code(200);
    echo json_encode([
        'modulo'   => 'Gestión Profesoral',
        'version'  => '2.0.0',
        'estado'   => 'operativo',
        'servidor' => 'PHP ' . PHP_VERSION,
        'rutas'    => [
            'v1' => [
                '/api/programa',
                '/api/red',
                '/api/linea-investigacion',
                '/api/termino-clave',
                '/api/area-conocimiento'
            ],
            'v2' => [
                '/api/docente',
                '/api/estudios-realizados',
                '/api/evaluacion-docente',
                '/api/experiencia',
                '/api/reconocimiento',
                '/api/docente-departamento',
                '/api/intereses-futuros',
                '/api/red-docente',
                '/api/estudio-ac',
                '/api/apoyo-profesoral',
                '/api/beca'
            ]
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if (str_starts_with($uri, '/api/')) {
    $partes = explode('/', trim($uri, '/'));
    $recurso = $partes[1] ?? '';

    switch ($recurso) {
        // =====================================================================
        // V1: CATÁLOGOS BASE
        // =====================================================================
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

        case 'red':
            $controlador = new ApiGestion\Controladores\ControladorRed(obtenerServicioRed($conexion));
            $idr = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $idr !== null ? $controlador->obtenerUno($idr) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $idr !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($idr, $cuerpo);
            } elseif ($metodo === 'PATCH' && $idr !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($idr, $cuerpo);
            } elseif ($metodo === 'DELETE' && $idr !== null) {
                $controlador->eliminar($idr);
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

        // =====================================================================
        // V2: MAESTRO-DETALLE DOCENTE Y TABLAS DEPENDIENTES
        // =====================================================================
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

        // =====================================================================
        // V2: VINCULACIONES, INTERESES, REDES, ÁREAS, APOYOS Y BECAS
        // =====================================================================
        case 'docente-departamento':
            $controlador = new ApiGestion\Controladores\ControladorDocenteDepartamento(obtenerServicioDocenteDepartamento($conexion));
            $docente = isset($partes[2]) ? (int)$partes[2] : null;
            $departamento = isset($partes[3]) ? (int)$partes[3] : null;

            if ($metodo === 'GET') {
                ($docente !== null && $departamento !== null)
                    ? $controlador->obtenerUno($docente, $departamento)
                    : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $docente !== null && $departamento !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($docente, $departamento, $cuerpo);
            } elseif ($metodo === 'PATCH' && $docente !== null && $departamento !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($docente, $departamento, $cuerpo);
            } elseif ($metodo === 'DELETE' && $docente !== null && $departamento !== null) {
                $controlador->eliminar($docente, $departamento);
            }
            break;

        case 'intereses-futuros':
            $controlador = new ApiGestion\Controladores\ControladorInteresFuturo(obtenerServicioInteresFuturo($conexion));
            $docente = isset($partes[2]) ? (int)$partes[2] : null;
            $termino = isset($partes[3]) ? urldecode($partes[3]) : null;

            if ($metodo === 'GET') {
                ($docente !== null && $termino !== null)
                    ? $controlador->obtenerUno($docente, $termino)
                    : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'DELETE' && $docente !== null && $termino !== null) {
                $controlador->eliminar($docente, $termino);
            }
            break;

        case 'red-docente':
            $controlador = new ApiGestion\Controladores\ControladorRedDocente(obtenerServicioRedDocente($conexion));
            $red = isset($partes[2]) ? (int)$partes[2] : null;
            $docente = isset($partes[3]) ? (int)$partes[3] : null;

            if ($metodo === 'GET') {
                ($red !== null && $docente !== null)
                    ? $controlador->obtenerUno($red, $docente)
                    : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $red !== null && $docente !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($red, $docente, $cuerpo);
            } elseif ($metodo === 'PATCH' && $red !== null && $docente !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($red, $docente, $cuerpo);
            } elseif ($metodo === 'DELETE' && $red !== null && $docente !== null) {
                $controlador->eliminar($red, $docente);
            }
            break;

        case 'estudio-ac':
            $controlador = new ApiGestion\Controladores\ControladorEstudioAreaConocimiento(obtenerServicioEstudioAreaConocimiento($conexion));
            $estudio = isset($partes[2]) ? (int)$partes[2] : null;
            $area = isset($partes[3]) ? urldecode($partes[3]) : null;

            if ($metodo === 'GET') {
                ($estudio !== null && $area !== null)
                    ? $controlador->obtenerUno($estudio, $area)
                    : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'DELETE' && $estudio !== null && $area !== null) {
                $controlador->eliminar($estudio, $area);
            }
            break;

        case 'apoyo-profesoral':
            $controlador = new ApiGestion\Controladores\ControladorApoyoProfesoral(obtenerServicioApoyoProfesoral($conexion));
            $estudios = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $estudios !== null ? $controlador->obtenerUno($estudios) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $estudios !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($estudios, $cuerpo);
            } elseif ($metodo === 'PATCH' && $estudios !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($estudios, $cuerpo);
            } elseif ($metodo === 'DELETE' && $estudios !== null) {
                $controlador->eliminar($estudios);
            }
            break;

        case 'beca':
            $controlador = new ApiGestion\Controladores\ControladorBeca(obtenerServicioBeca($conexion));
            $estudios = isset($partes[2]) ? (int)$partes[2] : null;

            if ($metodo === 'GET') {
                $estudios !== null ? $controlador->obtenerUno($estudios) : $controlador->listar($_GET);
            } elseif ($metodo === 'POST') {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->crear($cuerpo);
            } elseif ($metodo === 'PUT' && $estudios !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->reemplazar($estudios, $cuerpo);
            } elseif ($metodo === 'PATCH' && $estudios !== null) {
                $cuerpo = json_decode(file_get_contents('php://input'), true) ?? [];
                $controlador->actualizarParcial($estudios, $cuerpo);
            } elseif ($metodo === 'DELETE' && $estudios !== null) {
                $controlador->eliminar($estudios);
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
