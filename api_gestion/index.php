<?php
/**
 * Enrutador único de la API de Gestión Profesoral (v3).
 *
 * Responsabilidades (y nada más):
 *   1. Cargar clases (autocarga PSR-4 casera, sin Composer — Artículo 2).
 *   2. Leer método, ruta y cuerpo JSON de la petición.
 *   3. Aplicar el guardia de seguridad:
 *        - Rutas públicas:  GET /  y  POST /api/login
 *        - Todo lo demás:   exigirSesion()  -> 401
 *        - /api/usuario, /api/rol, /api/rol-usuario: exigirRol('admin') -> 403
 *        - POST / PUT / PATCH / DELETE en el resto: exigirEscritura() -> 403 para 'consulta'
 *   4. Delegar en el controlador del recurso. El enrutador NO contiene SQL ni
 *      lógica de negocio; los controladores se construyen en ensamblador.php.
 */

declare(strict_types=1);

// -----------------------------------------------------------------------------
// 1. Autocarga: ApiGestion\{Controladores,Servicios,Repositorios,Modelos,Excepciones}
// -----------------------------------------------------------------------------
spl_autoload_register(static function (string $clase): void {
    $prefijo = 'ApiGestion\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }
    $relativa = substr($clase, strlen($prefijo));              // p. ej. Servicios\ServicioRed
    $partes = explode('\\', $relativa);
    $partes[0] = strtolower($partes[0]);                       // Servicios -> servicios
    $archivo = __DIR__ . '/' . implode('/', $partes) . '.php';
    if (is_file($archivo)) {
        require_once $archivo;
    }
});

require_once __DIR__ . '/autorizacion/guardia.php';
require_once __DIR__ . '/servicios/ensamblador.php';

// -----------------------------------------------------------------------------
// 2. Tabla de recursos
//    'llaves': tipo de cada segmento de la ruta tras el recurso (i = entero, s = texto)
//    'admin' : solo rol admin (Matriz RBAC de la spec v3)
// -----------------------------------------------------------------------------
const RECURSOS = [
    // v1
    'programa'             => ['llaves' => ['i']],
    'red'                  => ['llaves' => ['i']],
    'linea-investigacion'  => ['llaves' => ['i']],
    'termino-clave'        => ['llaves' => ['s']],
    'area-conocimiento'    => ['llaves' => ['s']],
    // v2
    'docente'              => ['llaves' => ['i']],
    'estudios-realizados'  => ['llaves' => ['i']],
    'evaluacion-docente'   => ['llaves' => ['i']],
    'experiencia'          => ['llaves' => ['i']],
    'reconocimiento'       => ['llaves' => ['i']],
    'docente-departamento' => ['llaves' => ['i', 'i']],
    'intereses-futuros'    => ['llaves' => ['i', 's']],
    'red-docente'          => ['llaves' => ['i', 'i']],
    'estudio-ac'           => ['llaves' => ['i', 's']],
    'apoyo-profesoral'     => ['llaves' => ['i']],
    'beca'                 => ['llaves' => ['i']],
];

const RECURSOS_ADMIN = ['usuario', 'rol', 'rol-usuario'];

// -----------------------------------------------------------------------------
// Utilidades de respuesta
// -----------------------------------------------------------------------------
function responderJsonYSalir(int $estado, array $cuerpo): never
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Convierte los segmentos de la URL en argumentos tipados. Devuelve null si algún
 * segmento entero no es numérico (la ruta no existe -> 404).
 *
 * @param string[] $segmentos
 * @param string[] $tipos
 * @return array<int, int|string>|null
 */
function convertirLlaves(array $segmentos, array $tipos): ?array
{
    $args = [];
    foreach ($segmentos as $i => $segmento) {
        $valor = rawurldecode($segmento);
        if (($tipos[$i] ?? 's') === 'i') {
            if (!preg_match('/^-?\d+$/', $valor)) {
                return null;
            }
            $args[] = (int) $valor;
        } else {
            $args[] = $valor;
        }
    }
    return $args;
}

// -----------------------------------------------------------------------------
// 3. Despacho
// -----------------------------------------------------------------------------
try {
    $metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $ruta = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($ruta === '') {
        $ruta = '/';
    }

    // Las peticiones preflight no llevan token ni cuerpo
    if ($metodo === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    // --- Ruta pública: diagnóstico -------------------------------------------
    if ($ruta === '/') {
        if ($metodo !== 'GET') {
            responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo no permitido.']);
        }
        responderJsonYSalir(200, [
            'mensaje'  => 'API de Gestion Profesoral en linea.',
            'version'  => 'v3',
            'recursos' => array_merge(array_keys(RECURSOS), RECURSOS_ADMIN),
        ]);
    }

    if (!str_starts_with($ruta, '/api/')) {
        responderJsonYSalir(404, ['estado' => 404, 'mensaje' => 'Ruta no encontrada.']);
    }

    // --- Cuerpo JSON ---------------------------------------------------------
    $cuerpo = [];
    if (in_array($metodo, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $crudo = (string) file_get_contents('php://input');
        if (trim($crudo) !== '') {
            $decodificado = json_decode($crudo, true);
            if (!is_array($decodificado)) {
                responderJsonYSalir(400, [
                    'estado'  => 400,
                    'mensaje' => 'El cuerpo de la peticion no es un JSON valido.',
                ]);
            }
            $cuerpo = $decodificado;
        }
    }

    $segmentos = explode('/', substr($ruta, strlen('/api/')));
    $recurso = array_shift($segmentos);

    // --- Ruta pública: login -------------------------------------------------
    if ($recurso === 'login') {
        if ($metodo !== 'POST' || $segmentos !== []) {
            responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo no permitido. Use POST.']);
        }
        obtenerControlador('login', obtenerConexion())->entrar($cuerpo);
        exit;
    }

    // --- Guardia: desde aquí todo exige sesión -------------------------------
    $quien = exigirSesion();                         // 401 + WWW-Authenticate

    if ($recurso === 'sesion') {
        $controlador = obtenerControlador('sesion', obtenerConexion());
        match ($metodo) {
            'GET'    => $controlador->quienSoy($quien),
            'DELETE' => $controlador->salir($quien),
            default  => responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo no permitido.']),
        };
        exit;
    }

    $esAdmin = in_array($recurso, RECURSOS_ADMIN, true);
    if (!$esAdmin && !isset(RECURSOS[$recurso])) {
        responderJsonYSalir(404, ['estado' => 404, 'mensaje' => 'Recurso no encontrado.']);
    }

    if ($esAdmin) {
        exigirRol('admin');                          // 403 para docente y consulta
    } elseif ($metodo !== 'GET') {
        exigirEscritura();                           // 403 para consulta
    }

    $controlador = obtenerControlador($recurso, obtenerConexion());

    // --- Recursos de administración -----------------------------------------
    if ($recurso === 'rol-usuario') {
        match (true) {
            $metodo === 'GET' && $segmentos === []        => $controlador->listar(),
            $metodo === 'GET' && count($segmentos) === 1 && ctype_digit($segmentos[0])
                                                          => $controlador->obtenerRolesPorUsuario((int) $segmentos[0]),
            $metodo === 'POST' && $segmentos === []       => $controlador->asignar($cuerpo),
            $metodo === 'DELETE' && $segmentos === []     => $controlador->desasignar($cuerpo),
            default => responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo o ruta no permitidos.']),
        };
        exit;
    }

    if ($esAdmin) {                                  // usuario y rol: mismo contrato
        $id = (count($segmentos) === 1 && ctype_digit($segmentos[0])) ? (int) $segmentos[0] : null;
        match (true) {
            $metodo === 'GET' && $segmentos === []                     => $controlador->listar(),
            $metodo === 'GET' && $id !== null                          => $controlador->obtenerPorId($id),
            $metodo === 'POST' && $segmentos === []                    => $controlador->crear($cuerpo),
            in_array($metodo, ['PUT', 'PATCH'], true) && $id !== null  => $controlador->actualizar($id, $cuerpo),
            $metodo === 'DELETE' && $id !== null                       => $controlador->eliminar($id),
            default => responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo o ruta no permitidos.']),
        };
        exit;
    }

    // --- Recursos académicos (v1 y v2) --------------------------------------
    $tipos = RECURSOS[$recurso]['llaves'];
    $llaves = [];
    if ($segmentos !== []) {
        if (count($segmentos) !== count($tipos)) {
            responderJsonYSalir(404, ['estado' => 404, 'mensaje' => 'Ruta no encontrada.']);
        }
        $llaves = convertirLlaves($segmentos, $tipos);
        if ($llaves === null) {
            responderJsonYSalir(404, ['estado' => 404, 'mensaje' => 'Registro no encontrado.']);
        }
    }

    $accion = match (true) {
        $metodo === 'GET'    && $segmentos === [] => ['listar', [$_GET]],
        $metodo === 'GET'                         => ['obtenerUno', $llaves],
        $metodo === 'POST'   && $segmentos === [] => ['crear', [$cuerpo]],
        $metodo === 'PUT'    && $segmentos !== [] => ['reemplazar', [...$llaves, $cuerpo]],
        $metodo === 'PATCH'  && $segmentos !== [] => ['actualizarParcial', [...$llaves, $cuerpo]],
        $metodo === 'DELETE' && $segmentos !== [] => ['eliminar', $llaves],
        default                                   => null,
    };

    if ($accion === null || !method_exists($controlador, $accion[0])) {
        responderJsonYSalir(405, ['estado' => 405, 'mensaje' => 'Metodo no permitido para este recurso.']);
    }

    $controlador->{$accion[0]}(...$accion[1]);
} catch (Throwable $e) {
    responderJsonYSalir(500, [
        'estado'  => 500,
        'mensaje' => 'Error interno del servidor.',
        'detalle' => $e->getMessage(),
    ]);
}
