<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/cliente_api.php';

// Servir archivos estáticos directamente si se usa el servidor embebido de PHP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (file_exists(__DIR__ . '/publico' . $uri) && is_file(__DIR__ . '/publico' . $uri)) {
    return false;
}

$metodo = $_SERVER['REQUEST_METHOD'];

// Capturar mensajes flash o errores de sesión
$mensajeExito = $_GET['exito'] ?? null;
$mensajeError = $_GET['error'] ?? null;

// =============================================================================
// v3: funciones de apoyo del enrutador web
// =============================================================================

/** Mapa cédula => "Nombres Apellidos" para mostrar nombres en las listas con FK a docente. */
function mapa_docentes(): array
{
    $mapa = [];
    foreach ((listar_docentes()['datos'] ?? []) as $d) {
        $mapa[(int)$d['cedula']] = trim(($d['nombres'] ?? '') . ' ' . ($d['apellidos'] ?? ''));
    }
    return $mapa;
}

/** Lista de mensajes de error legibles a partir de la respuesta de la API. */
function errores_de_respuesta(array $res, string $defecto): array
{
    $cuerpo = $res['cuerpo'] ?? [];
    if (!empty($cuerpo['errores']) && is_array($cuerpo['errores'])) {
        return $cuerpo['errores'];
    }
    if ($res['codigo'] === 0) {
        return ['No se pudo conectar con el servidor de la API.'];
    }
    return [$cuerpo['mensaje'] ?? $defecto];
}

/** Convierte los campos de formulario (todo llega como texto) a los tipos JSON que exige la API. */
function tipar_campos(array $datos, array $enteros = [], array $decimales = [], array $nulables = []): array
{
    foreach ($datos as $campo => $valor) {
        if (!is_string($valor)) {
            continue;
        }
        if (in_array($campo, $nulables, true) && trim($valor) === '') {
            $datos[$campo] = null;
        } elseif (in_array($campo, $enteros, true) && is_numeric($valor)) {
            $datos[$campo] = (int)$valor;
        } elseif (in_array($campo, $decimales, true) && is_numeric($valor)) {
            $datos[$campo] = (float)$valor;
        }
    }
    return $datos;
}

/** Filas "detalle_*[]" del formulario de docente -> arreglo de estudios para el maestro-detalle. */
function estudios_desde_post(array $post): array
{
    $estudios = [];
    foreach (($post['detalle_titulo'] ?? []) as $i => $titulo) {
        if (trim((string)$titulo) === '') {
            continue;
        }
        $estudios[] = [
            'titulo'          => trim((string)$titulo),
            'universidad'     => trim((string)($post['detalle_universidad'][$i] ?? '')),
            'tipo'            => (string)($post['detalle_tipo'][$i] ?? ''),
            'fecha'           => (string)($post['detalle_fecha'][$i] ?? ''),
            'ciudad'          => trim((string)($post['detalle_ciudad'][$i] ?? '')),
            'pais'            => trim((string)($post['detalle_pais'][$i] ?? '')),
            'metodologia'     => (string)($post['detalle_metodologia'][$i] ?? 'Presencial'),
            'ins_acreditada'  => (int)($post['detalle_ins_acreditada'][$i] ?? 0),
            'perfil_egresado' => trim((string)($post['detalle_perfil_egresado'][$i] ?? '')),
        ];
    }
    return $estudios;
}

/** Campos del docente (sin los detalle_*) listos para enviar a la API. */
function docente_desde_post(array $post): array
{
    $campos = ['cedula', 'nombres', 'apellidos', 'genero', 'cargo', 'fecha_nacimiento', 'correo',
        'telefono', 'url_cvlac', 'fecha_actualizacion', 'escalafon', 'perfil', 'cat_minciencia',
        'conv_minciencia', 'nacionalidaad', 'linea_investigacion_principal'];
    $datos = [];
    foreach ($campos as $campo) {
        $datos[$campo] = $post[$campo] ?? null;
    }
    return tipar_campos($datos, ['cedula', 'linea_investigacion_principal'], [],
        ['linea_investigacion_principal', 'cat_minciencia']);
}

/**
 * Entidades con CRUD simple (una FK a docente). Cada una usa sus propias vistas y
 * funciones del cliente; el despacho común evita repetir seis bloques idénticos.
 */
function entidad_simple(string $uri): ?array
{
    $entidades = [
        '/estudios' => [
            'nuevo' => 'nuevo', 'lista' => 'lista_estudios', 'form' => 'formulario_estudio',
            'var_lista' => 'estudios', 'var_form' => 'estudio', 'etiqueta' => 'Estudio',
            'listar' => 'listar_estudios_realizados', 'obtener' => 'obtener_estudio_realizado',
            'crear' => 'crear_estudio_realizado', 'reemplazar' => 'reemplazar_estudio_realizado',
            'parcial' => 'actualizar_parcial_estudio_realizado', 'retirar' => 'retirar_estudio_realizado',
            'enteros' => ['docente', 'ins_acreditada'], 'decimales' => [], 'nulables' => [],
        ],
        '/evaluaciones' => [
            'nuevo' => 'nueva', 'lista' => 'lista_evaluaciones', 'form' => 'formulario_evaluacion',
            'var_lista' => 'evaluaciones', 'var_form' => 'evaluacion', 'etiqueta' => 'Evaluación',
            'listar' => 'listar_evaluaciones_docente', 'obtener' => 'obtener_evaluacion_docente',
            'crear' => 'crear_evaluacion_docente', 'reemplazar' => 'reemplazar_evaluacion_docente',
            'parcial' => 'actualizar_parcial_evaluacion_docente', 'retirar' => 'retirar_evaluacion_docente',
            'enteros' => ['docente'], 'decimales' => ['calificacion'], 'nulables' => [],
        ],
        '/experiencias' => [
            'nuevo' => 'nueva', 'lista' => 'lista_experiencias', 'form' => 'formulario_experiencia',
            'var_lista' => 'experiencias', 'var_form' => 'experiencia', 'etiqueta' => 'Experiencia',
            'listar' => 'listar_experiencias', 'obtener' => 'obtener_experiencia',
            'crear' => 'crear_experiencia', 'reemplazar' => 'reemplazar_experiencia',
            'parcial' => 'actualizar_parcial_experiencia', 'retirar' => 'retirar_experiencia',
            'enteros' => ['docente'], 'decimales' => [], 'nulables' => ['fecha_fin'],
        ],
        '/reconocimientos' => [
            'nuevo' => 'nuevo', 'lista' => 'lista_reconocimientos', 'form' => 'formulario_reconocimiento',
            'var_lista' => 'reconocimientos', 'var_form' => 'reconocimiento', 'etiqueta' => 'Reconocimiento',
            'listar' => 'listar_reconocimientos', 'obtener' => 'obtener_reconocimiento',
            'crear' => 'crear_reconocimiento', 'reemplazar' => 'reemplazar_reconocimiento',
            'parcial' => 'actualizar_parcial_reconocimiento', 'retirar' => 'retirar_reconocimiento',
            'enteros' => ['docente'], 'decimales' => [], 'nulables' => [],
        ],
    ];

    foreach ($entidades as $base => $cfg) {
        if ($uri === $base) {
            return [$cfg + ['base' => $base], 'lista'];
        }
        if (str_starts_with($uri, $base . '/')) {
            $resto = substr($uri, strlen($base) + 1);
            $acciones = [$cfg['nuevo'] => 'nuevo', 'crear' => 'crear', 'editar' => 'editar',
                'guardar-edicion' => 'guardar-edicion', 'retirar' => 'retirar'];
            if (isset($acciones[$resto])) {
                return [$cfg + ['base' => $base], $acciones[$resto]];
            }
        }
    }
    return null;
}

// =============================================================================
// v3: sesión y control de acceso en el front (la API es quien realmente protege)
// =============================================================================
$rolesSesion   = array_map('strtolower', (array)($_SESSION['roles'] ?? []));
$esAdmin       = in_array('admin', $rolesSesion, true);
$puedeEscribir = $esAdmin || in_array('docente', $rolesSesion, true);
$accesoDenegado = null;

if ($uri !== '/entrar') {
    // Sin token o con token vencido: de vuelta al login
    if (empty($_SESSION['token']) || (int)($_SESSION['expira'] ?? 0) < time()) {
        $habiaSesion = !empty($_SESSION['token']);
        $_SESSION = [];
        header('Location: /entrar' . ($habiaSesion ? '?error=' . urlencode('Su sesión expiró. Inicie sesión de nuevo.') : ''));
        exit;
    }
    // Rol consulta: solo lectura (se oculta la UI de escritura y se rechaza cualquier intento)
    $esRutaDeEscritura = $metodo !== 'GET' || (bool)preg_match('#/(nuevo|nueva|crear|editar|retirar-directo)$#', $uri);
    if (!$puedeEscribir && $esRutaDeEscritura && $uri !== '/salir') {
        $accesoDenegado = 'Su rol no tiene permiso para esta operacion.';
    }
    // Usuarios y roles: solo administrador
    if (!$esAdmin && (str_starts_with($uri, '/usuarios') || str_starts_with($uri, '/roles'))) {
        $accesoDenegado = 'Su rol no tiene permiso para esta operacion.';
    }
}

ob_start();

if ($accesoDenegado !== null) {
    http_response_code(403);
    echo '<div class="alert alert-danger shadow-sm"><h4 class="alert-heading">403 — Acceso denegado</h4><p class="mb-0">'
        . htmlspecialchars($accesoDenegado) . '</p></div>';
} elseif ($uri === '/entrar') {
    if (!empty($_SESSION['token']) && (int)($_SESSION['expira'] ?? 0) >= time()) {
        header('Location: /');
        exit;
    }
    if ($metodo === 'POST') {
        $res = iniciar_sesion_api(trim((string)($_POST['username'] ?? '')), (string)($_POST['password'] ?? ''));
        if ($res['codigo'] === 200 && !empty($res['cuerpo']['token'])) {
            session_regenerate_id(true);
            $_SESSION['token']   = $res['cuerpo']['token'];
            $_SESSION['expira']  = (int)($res['cuerpo']['expira'] ?? 0);
            $_SESSION['usuario'] = $res['cuerpo']['usuario'] ?? [];
            $_SESSION['roles']   = $res['cuerpo']['roles'] ?? [];
            header('Location: /');
            exit;
        }
        http_response_code($res['codigo'] === 401 ? 401 : 200);
        $mensajeError = errores_de_respuesta($res, 'No se pudo iniciar sesión.')[0];
    }
    require __DIR__ . '/vistas/entrar.php';
} elseif ($uri === '/salir') {
    cerrar_sesion_api();                       // best-effort: la API solo confirma
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], (bool)$c['secure'], (bool)$c['httponly']);
    }
    session_destroy();
    header('Location: /entrar?exito=' . urlencode('Sesión cerrada correctamente.'));
    exit;
} elseif ($uri === '/' || $uri === '') {
    require __DIR__ . '/vistas/inicio.php';
} elseif ($uri === '/programas') {
    $respuesta = listar_programas();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $programas = [];
    } else {
        $programas = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista.php';
} elseif ($uri === '/programas/nuevo') {
    $programa = []; // <- Inicializar siempre como arreglo vacío
    $errores  = [];
    require __DIR__ . '/vistas/formulario.php';
} elseif ($uri === '/programas/crear' && $metodo === 'POST') {
    $res = crear_programa($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /programas?exito=' . urlencode('Programa creado correctamente.'));
        exit;
    } else {
        $programa = $_POST; // <- Mantener los datos ingresados
        $errores  = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el programa.'];
        require __DIR__ . '/vistas/formulario.php';
    }
} elseif ($uri === '/programas/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $programa = obtener_programa($id);
    if (isset($programa['error']) || isset($programa['estado']) && $programa['estado'] === 404) {
        header('Location: /programas?error=' . urlencode('El programa seleccionado no existe.'));
        exit;
    }
    $errores = [];
    require __DIR__ . '/vistas/formulario.php';
} elseif ($uri === '/programas/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_programa($id, $_POST);
    } else {
        // Filtrar campos para enviar solo lo diligenciado (PATCH)
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['id'], $datos['accion']);
        $res = actualizar_parcial_programa($id, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /programas?exito=' . urlencode('Programa modificado correctamente.'));
        exit;
    } else {
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar el programa.'];
        $programa = $_POST;
        require __DIR__ . '/vistas/formulario.php';
    }
} elseif ($uri === '/programas/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_programa($id);
    if ($res['codigo'] === 200) {
        header('Location: /programas?exito=' . urlencode('Programa retirado con éxito.'));
    } else {
        header('Location: /programas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el programa.'));
    }
    exit;
} elseif ($uri === '/redes') {
    $respuesta = listar_redes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $redes = [];
    } else {
        $redes = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_redes.php';
} elseif ($uri === '/redes/nueva') {
    $red = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_red.php';
} elseif ($uri === '/redes/crear' && $metodo === 'POST') {
    $res = crear_red($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /redes?exito=' . urlencode('Red creada correctamente.'));
        exit;
    } else {
        $red = $_POST; // se conserva lo que la persona escribió
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear la red.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_red.php';
    }
} elseif ($uri === '/redes/editar') {
    $idr = (int)($_GET['idr'] ?? 0);
    $red = obtener_red($idr);
    if (isset($red['error']) || (isset($red['estado']) && $red['estado'] === 404)) {
        header('Location: /redes?error=' . urlencode('La red seleccionada no existe.'));
        exit;
    }
    $errores = [];
    $esEdicion = true;
    require __DIR__ . '/vistas/formulario_red.php';
} elseif ($uri === '/redes/guardar-edicion' && $metodo === 'POST') {
    $idr = (int)($_POST['idr'] ?? 0);
    $accion = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_red($idr, $_POST);
    } else {
        // Solo se envía lo que la persona diligenció (PATCH)
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['idr'], $datos['accion']);
        $res = actualizar_parcial_red($idr, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /redes?exito=' . urlencode('Red modificada correctamente.'));
        exit;
    } else {
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar la red.'];
        $red = $_POST;
        $esEdicion = true;
        require __DIR__ . '/vistas/formulario_red.php';
    }
} elseif ($uri === '/redes/retirar' && $metodo === 'POST') {
    $idr = (int)($_POST['idr'] ?? 0);
    $res = retirar_red($idr);
    if ($res['codigo'] === 200) {
        header('Location: /redes?exito=' . urlencode('Red retirada con éxito.'));
    } else {
        header('Location: /redes?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la red.'));
    }
    exit;
} elseif ($uri === '/lineas') {
    $respuesta = listar_lineas();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $lineas = [];
    } else {
        $lineas = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_lineas.php';
} elseif ($uri === '/lineas/nueva') {
    $linea = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_linea.php';
} elseif ($uri === '/lineas/crear' && $metodo === 'POST') {
    $res = crear_linea($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /lineas?exito=' . urlencode('Línea de investigación creada correctamente.'));
        exit;
    } else {
        $linea = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear la línea.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_linea.php';
    }
} elseif ($uri === '/lineas/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $linea = obtener_linea($id);
    if (isset($linea['error']) || (isset($linea['estado']) && $linea['estado'] === 404)) {
        header('Location: /lineas?error=' . urlencode('La línea seleccionada no existe.'));
        exit;
    }
    $errores = [];
    $esEdicion = true;
    require __DIR__ . '/vistas/formulario_linea.php';
} elseif ($uri === '/lineas/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_linea($id, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['id'], $datos['accion']);
        $res = actualizar_parcial_linea($id, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /lineas?exito=' . urlencode('Línea de investigación modificada correctamente.'));
        exit;
    } else {
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar la línea.'];
        $linea = $_POST;
        $esEdicion = true;
        require __DIR__ . '/vistas/formulario_linea.php';
    }
} elseif ($uri === '/lineas/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_linea($id);
    if ($res['codigo'] === 200) {
        header('Location: /lineas?exito=' . urlencode('Línea de investigación retirada con éxito.'));
    } else {
        header('Location: /lineas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la línea.'));
    }
    exit;
} elseif ($uri === '/terminos') {
    $respuesta = listar_terminos();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $terminos = [];
    } else {
        $terminos = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_terminos.php';
} elseif ($uri === '/terminos/nuevo') {
    $termino = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_termino.php';
} elseif ($uri === '/terminos/crear' && $metodo === 'POST') {
    $res = crear_termino($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /terminos?exito=' . urlencode('Término clave creado correctamente.'));
        exit;
    } else {
        $termino = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el término.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_termino.php';
    }
} elseif ($uri === '/terminos/editar') {
    $clave = (string)($_GET['termino'] ?? '');
    $termino = obtener_termino($clave);
    if (isset($termino['error']) || (isset($termino['estado']) && $termino['estado'] === 404)) {
        header('Location: /terminos?error=' . urlencode('El término seleccionado no existe.'));
        exit;
    }
    $errores = [];
    $esEdicion = true;
    require __DIR__ . '/vistas/formulario_termino.php';
} elseif ($uri === '/terminos/guardar-edicion' && $metodo === 'POST') {
    $clave = (string)($_POST['termino'] ?? '');
    $accion = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_termino($clave, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['termino'], $datos['accion']);
        $res = actualizar_parcial_termino($clave, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /terminos?exito=' . urlencode('Término clave modificado correctamente.'));
        exit;
    } else {
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar el término.'];
        $termino = $_POST;
        $esEdicion = true;
        require __DIR__ . '/vistas/formulario_termino.php';
    }
} elseif ($uri === '/terminos/retirar' && $metodo === 'POST') {
    $clave = (string)($_POST['termino'] ?? '');
    $res = retirar_termino($clave);
    if ($res['codigo'] === 200) {
        header('Location: /terminos?exito=' . urlencode('Término clave retirado con éxito.'));
    } else {
        header('Location: /terminos?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el término.'));
    }
    exit;

    } elseif ($uri === '/areas') {
    $respuesta = listar_areas();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $areas = [];
    } else {
        $areas = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_areas.php';
} elseif ($uri === '/areas/nueva') {
    $area = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_area.php';
} elseif ($uri === '/areas/crear' && $metodo === 'POST') {
    $res = crear_area($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /areas?exito=' . urlencode('Área de conocimiento creada correctamente.'));
        exit;
    } else {
        $area = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el área.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_area.php';
    }
} elseif ($uri === '/areas/editar') {
    $clave = (string)($_GET['id'] ?? '');
    $area = obtener_area($clave);
    if (isset($area['error']) || (isset($area['estado']) && $area['estado'] === 404)) {
        header('Location: /areas?error=' . urlencode('El área seleccionada no existe.'));
        exit;
    }
    $errores = [];
    $esEdicion = true;
    require __DIR__ . '/vistas/formulario_area.php';
} elseif ($uri === '/areas/guardar-edicion' && $metodo === 'POST') {
    $clave = (string)($_POST['id'] ?? '');
    $accion = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_area($clave, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['id'], $datos['accion']);
        $res = actualizar_parcial_area($clave, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /areas?exito=' . urlencode('Área de conocimiento modificada correctamente.'));
        exit;
    } else {
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar el área.'];
        $area = $_POST;
        $esEdicion = true;
        require __DIR__ . '/vistas/formulario_area.php';
    }
} elseif ($uri === '/areas/retirar' && $metodo === 'POST') {
    $clave = (string)($_POST['id'] ?? '');
    $res = retirar_area($clave);
    if ($res['codigo'] === 200) {
        header('Location: /areas?exito=' . urlencode('Área de conocimiento retirada con éxito.'));
    } else {
        header('Location: /areas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el área.'));
    }
    exit;

// ============================================================================
// RUTAS V2 — MÓDULO GESTIÓN PROFESORAL
// ============================================================================

// --- 1. DOCENTE - DEPARTAMENTO ---
} elseif ($uri === '/docente-departamentos') {
    $respuesta = listar_docente_departamentos();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $vinculaciones = [];
    } else {
        $vinculaciones = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_docente_departamento.php';
} elseif ($uri === '/docente-departamentos/nuevo') {
    $registro  = [];
    $errores   = [];
    $esEdicion = false;
    $docentes  = listar_docentes()['datos'] ?? [];
    $programas = listar_programas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente_departamento.php';
} elseif ($uri === '/docente-departamentos/crear' && $metodo === 'POST') {
    $res = crear_docente_departamento($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /docente-departamentos?exito=' . urlencode('Vinculación creada correctamente.'));
        exit;
    } else {
        $registro  = $_POST;
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear la vinculación.'];
        $esEdicion = false;
        $docentes  = listar_docentes()['datos'] ?? [];
        $programas = listar_programas()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_docente_departamento.php';
    }
} elseif ($uri === '/docente-departamentos/editar') {
    $docente      = (int)($_GET['docente'] ?? 0);
    $departamento = (int)($_GET['departamento'] ?? 0);
    $registro     = obtener_docente_departamento($docente, $departamento);
    if (isset($registro['error']) || (isset($registro['estado']) && $registro['estado'] === 404)) {
        header('Location: /docente-departamentos?error=' . urlencode('La vinculación seleccionada no existe.'));
        exit;
    }
    $errores   = [];
    $esEdicion = true;
    $docentes  = listar_docentes()['datos'] ?? [];
    $programas = listar_programas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente_departamento.php';
} elseif ($uri === '/docente-departamentos/guardar-edicion' && $metodo === 'POST') {
    $docente      = (int)($_POST['docente'] ?? 0);
    $departamento = (int)($_POST['departamento'] ?? 0);
    $accion       = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_docente_departamento($docente, $departamento, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['docente'], $datos['departamento'], $datos['accion']);
        $res = actualizar_parcial_docente_departamento($docente, $departamento, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /docente-departamentos?exito=' . urlencode('Vinculación modificada correctamente.'));
        exit;
    } else {
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar la vinculación.'];
        $registro  = $_POST;
        $esEdicion = true;
        $docentes  = listar_docentes()['datos'] ?? [];
        $programas = listar_programas()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_docente_departamento.php';
    }
} elseif ($uri === '/docente-departamentos/retirar' && $metodo === 'POST') {
    $docente      = (int)($_POST['docente'] ?? 0);
    $departamento = (int)($_POST['departamento'] ?? 0);
    $res          = retirar_docente_departamento($docente, $departamento);
    if ($res['codigo'] === 200) {
        header('Location: /docente-departamentos?exito=' . urlencode('Vinculación retirada con éxito.'));
    } else {
        header('Location: /docente-departamentos?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la vinculación.'));
    }
    exit;

// --- 2. INTERESES FUTUROS (TABLA PUENTE) ---
} elseif ($uri === '/intereses-futuros') {
    $respuesta = listar_intereses_futuros();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $intereses = [];
    } else {
        $intereses = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_intereses_futuros.php';
} elseif ($uri === '/intereses-futuros/nuevo') {
    $registro = [];
    $errores  = [];
    $docentes = listar_docentes()['datos'] ?? [];
    $terminos = listar_terminos()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_intereses_futuros.php';
} elseif ($uri === '/intereses-futuros/crear' && $metodo === 'POST') {
    $res = crear_interes_futuro($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /intereses-futuros?exito=' . urlencode('Interés de investigación asignado correctamente.'));
        exit;
    } else {
        $registro = $_POST;
        $errores  = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al asignar el interés de investigación.'];
        $docentes = listar_docentes()['datos'] ?? [];
        $terminos = listar_terminos()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_intereses_futuros.php';
    }
} elseif ($uri === '/intereses-futuros/retirar' && $metodo === 'POST') {
    $docente = (int)($_POST['docente'] ?? 0);
    $termino = (string)($_POST['termino_clave'] ?? '');
    $res     = retirar_interes_futuro($docente, $termino);
    if ($res['codigo'] === 200) {
        header('Location: /intereses-futuros?exito=' . urlencode('Interés retirado con éxito.'));
    } else {
        header('Location: /intereses-futuros?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el interés.'));
    }
    exit;

// --- 3. RED DOCENTE ---
} elseif ($uri === '/redes-docentes') {
    $respuesta = listar_redes_docentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $vinculaciones = [];
    } else {
        $vinculaciones = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_red_docente.php';
} elseif ($uri === '/redes-docentes/nueva') {
    $registro  = [];
    $errores   = [];
    $esEdicion = false;
    $redes     = listar_redes()['datos'] ?? [];
    $docentes  = listar_docentes()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_red_docente.php';
} elseif ($uri === '/redes-docentes/crear' && $metodo === 'POST') {
    $res = crear_red_docente($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /redes-docentes?exito=' . urlencode('Participación en red registrada correctamente.'));
        exit;
    } else {
        $registro  = $_POST;
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar participación en la red.'];
        $esEdicion = false;
        $redes     = listar_redes()['datos'] ?? [];
        $docentes  = listar_docentes()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_red_docente.php';
    }
} elseif ($uri === '/redes-docentes/editar') {
    $red       = (int)($_GET['red'] ?? 0);
    $docente   = (int)($_GET['docente'] ?? 0);
    $registro  = obtener_red_docente($red, $docente);
    if (isset($registro['error']) || (isset($registro['estado']) && $registro['estado'] === 404)) {
        header('Location: /redes-docentes?error=' . urlencode('La vinculación a la red no existe.'));
        exit;
    }
    $errores   = [];
    $esEdicion = true;
    $redes     = listar_redes()['datos'] ?? [];
    $docentes  = listar_docentes()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_red_docente.php';
} elseif ($uri === '/redes-docentes/guardar-edicion' && $metodo === 'POST') {
    $red     = (int)($_POST['red'] ?? 0);
    $docente = (int)($_POST['docente'] ?? 0);
    $accion  = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_red_docente($red, $docente, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['red'], $datos['docente'], $datos['accion']);
        $res = actualizar_parcial_red_docente($red, $docente, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /redes-docentes?exito=' . urlencode('Participación modificada correctamente.'));
        exit;
    } else {
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar la participación.'];
        $registro  = $_POST;
        $esEdicion = true;
        $redes     = listar_redes()['datos'] ?? [];
        $docentes  = listar_docentes()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_red_docente.php';
    }
} elseif ($uri === '/redes-docentes/retirar' && $metodo === 'POST') {
    $red     = (int)($_POST['red'] ?? 0);
    $docente = (int)($_POST['docente'] ?? 0);
    $res     = retirar_red_docente($red, $docente);
    if ($res['codigo'] === 200) {
        header('Location: /redes-docentes?exito=' . urlencode('Participación retirada con éxito.'));
    } else {
        header('Location: /redes-docentes?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la participación.'));
    }
    exit;

// --- 4. ESTUDIO - ÁREA DE CONOCIMIENTO (TABLA PUENTE) ---
} elseif ($uri === '/estudios-areas') {
    $respuesta = listar_estudios_ac();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $asociaciones = [];
    } else {
        $asociaciones = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_estudio_ac.php';
} elseif ($uri === '/estudios-areas/nuevo') {
    $registro = [];
    $errores  = [];
    $estudios = listar_estudios_realizados()['datos'] ?? [];
    $areas    = listar_areas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_estudio_ac.php';
} elseif ($uri === '/estudios-areas/crear' && $metodo === 'POST') {
    $res = crear_estudio_ac($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /estudios-areas?exito=' . urlencode('Área de conocimiento asignada correctamente al estudio.'));
        exit;
    } else {
        $registro = $_POST;
        $errores  = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al asociar el área al estudio.'];
        $estudios = listar_estudios_realizados()['datos'] ?? [];
        $areas    = listar_areas()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_estudio_ac.php';
    }
} elseif ($uri === '/estudios-areas/retirar' && $metodo === 'POST') {
    $estudio = (int)($_POST['estudio'] ?? 0);
    $area    = (string)($_POST['area_conocimiento'] ?? '');
    $res     = retirar_estudio_ac($estudio, $area);
    if ($res['codigo'] === 200) {
        header('Location: /estudios-areas?exito=' . urlencode('Asociación retirada con éxito.'));
    } else {
        header('Location: /estudios-areas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la asociación.'));
    }
    exit;

// --- 5. APOYO PROFESORAL ---
} elseif ($uri === '/apoyos-profesorales') {
    $respuesta = listar_apoyos_profesorales();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $apoyos = [];
    } else {
        $apoyos = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_apoyo_profesoral.php';
} elseif ($uri === '/apoyos-profesorales/nuevo') {
    $registro  = [];
    $errores   = [];
    $esEdicion = false;
    $estudios  = listar_estudios_realizados()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_apoyo_profesoral.php';
} elseif ($uri === '/apoyos-profesorales/crear' && $metodo === 'POST') {
    $res = crear_apoyo_profesoral($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /apoyos-profesorales?exito=' . urlencode('Apoyo profesoral registrado correctamente.'));
        exit;
    } else {
        $registro  = $_POST;
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar el apoyo profesoral.'];
        $esEdicion = false;
        $estudios  = listar_estudios_realizados()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_apoyo_profesoral.php';
    }
} elseif ($uri === '/apoyos-profesorales/editar') {
    $estudios  = (int)($_GET['estudios'] ?? 0);
    $registro  = obtener_apoyo_profesoral($estudios);
    if (isset($registro['error']) || (isset($registro['estado']) && $registro['estado'] === 404)) {
        header('Location: /apoyos-profesorales?error=' . urlencode('El apoyo profesoral seleccionado no existe.'));
        exit;
    }
    $errores   = [];
    $esEdicion = true;
    $estudios  = listar_estudios_realizados()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_apoyo_profesoral.php';
} elseif ($uri === '/apoyos-profesorales/guardar-edicion' && $metodo === 'POST') {
    $estudios = (int)($_POST['estudios'] ?? 0);
    $accion   = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_apoyo_profesoral($estudios, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['estudios'], $datos['accion']);
        $res = actualizar_parcial_apoyo_profesoral($estudios, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /apoyos-profesorales?exito=' . urlencode('Apoyo profesoral modificado correctamente.'));
        exit;
    } else {
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar el apoyo profesoral.'];
        $registro  = $_POST;
        $esEdicion = true;
        $estudios  = listar_estudios_realizados()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_apoyo_profesoral.php';
    }
} elseif ($uri === '/apoyos-profesorales/retirar' && $metodo === 'POST') {
    $estudios = (int)($_POST['estudios'] ?? 0);
    $res      = retirar_apoyo_profesoral($estudios);
    if ($res['codigo'] === 200) {
        header('Location: /apoyos-profesorales?exito=' . urlencode('Apoyo profesoral retirado con éxito.'));
    } else {
        header('Location: /apoyos-profesorales?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el apoyo profesoral.'));
    }
    exit;

// --- 6. BECA ---
} elseif ($uri === '/becas') {
    $respuesta = listar_becas();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $becas = [];
    } else {
        $becas = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_beca.php';
} elseif ($uri === '/becas/nueva') {
    $registro  = [];
    $errores   = [];
    $esEdicion = false;
    $estudios  = listar_estudios_realizados()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_beca.php';
} elseif ($uri === '/becas/crear' && $metodo === 'POST') {
    $res = crear_beca($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /becas?exito=' . urlencode('Beca registrada correctamente.'));
        exit;
    } else {
        $registro  = $_POST;
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar la beca.'];
        $esEdicion = false;
        $estudios  = listar_estudios_realizados()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_beca.php';
    }
} elseif ($uri === '/becas/editar') {
    $estudios  = (int)($_GET['estudios'] ?? 0);
    $registro  = obtener_beca($estudios);
    if (isset($registro['error']) || (isset($registro['estado']) && $registro['estado'] === 404)) {
        header('Location: /becas?error=' . urlencode('La beca seleccionada no existe.'));
        exit;
    }
    $errores   = [];
    $esEdicion = true;
    $estudios  = listar_estudios_realizados()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_beca.php';
} elseif ($uri === '/becas/guardar-edicion' && $metodo === 'POST') {
    $estudios = (int)($_POST['estudios'] ?? 0);
    $accion   = $_POST['accion'] ?? 'reemplazar';

    if ($accion === 'reemplazar') {
        $res = reemplazar_beca($estudios, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['estudios'], $datos['accion']);
        $res = actualizar_parcial_beca($estudios, $datos);
    }

    if ($res['codigo'] === 200) {
        header('Location: /becas?exito=' . urlencode('Beca modificada correctamente.'));
        exit;
    } else {
        $errores   = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar la beca.'];
        $registro  = $_POST;
        $esEdicion = true;
        $estudios  = listar_estudios_realizados()['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_beca.php';
    }
} elseif ($uri === '/becas/retirar' && $metodo === 'POST') {
    $estudios = (int)($_POST['estudios'] ?? 0);
    $res      = retirar_beca($estudios);
    if ($res['codigo'] === 200) {
        header('Location: /becas?exito=' . urlencode('Beca retirada con éxito.'));
    } else {
        header('Location: /becas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la beca.'));
    }
    exit;

// ---------------------------------------------------------------------------
// DOCENTES (maestro-detalle con estudios)
// ---------------------------------------------------------------------------
} elseif ($uri === '/docentes') {
    $respuesta = listar_docentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
        $docentes = [];
    } else {
        $docentes = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_docentes.php';
} elseif ($uri === '/docentes/nuevo') {
    $docente = [];
    $errores = [];
    $esNuevo = true;
    $lineasInvestigacion = listar_lineas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/crear' && $metodo === 'POST') {
    $datos = docente_desde_post($_POST);
    $datos['estudios'] = estudios_desde_post($_POST);
    $res = crear_docente($datos);
    if (in_array($res['codigo'], [200, 201], true)) {
        header('Location: /docentes?exito=' . urlencode('Docente creado correctamente.'));
        exit;
    }
    $docente = $_POST;
    $errores = errores_de_respuesta($res, 'Error al crear el docente.');
    $esNuevo = true;
    $lineasInvestigacion = listar_lineas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/editar') {
    $cedula = (int)($_GET['cedula'] ?? 0);
    $docente = obtener_docente($cedula);
    if (isset($docente['error']) || (isset($docente['estado']) && $docente['estado'] === 404)) {
        header('Location: /docentes?error=' . urlencode('El docente seleccionado no existe.'));
        exit;
    }
    $errores = [];
    $lineasInvestigacion = listar_lineas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/guardar-edicion' && $metodo === 'POST') {
    $cedula = (int)($_POST['cedula'] ?? 0);
    $datos = docente_desde_post($_POST);
    $res = reemplazar_docente($cedula, $datos);
    $errores = [];
    if ($res['codigo'] === 200) {
        // Estudios agregados en esta edición: cada uno se crea ligado al docente
        foreach (estudios_desde_post($_POST) as $estudio) {
            $resEst = crear_estudio_realizado($estudio + ['docente' => $cedula]);
            if (!in_array($resEst['codigo'], [200, 201], true)) {
                $errores = array_merge($errores, errores_de_respuesta($resEst, 'No se pudo crear un estudio.'));
            }
        }
        if ($errores === []) {
            header('Location: /docentes?exito=' . urlencode('Docente modificado correctamente.'));
            exit;
        }
    } else {
        $errores = errores_de_respuesta($res, 'Error al modificar el docente.');
    }
    $docente = $_POST;
    $docente['estudios'] = obtener_docente($cedula)['estudios'] ?? [];
    $lineasInvestigacion = listar_lineas()['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/retirar' && $metodo === 'POST') {
    $res = retirar_docente((int)($_POST['cedula'] ?? 0));
    if ($res['codigo'] === 200) {
        header('Location: /docentes?exito=' . urlencode('Docente retirado correctamente.'));
    } else {
        header('Location: /docentes?error=' . urlencode(errores_de_respuesta($res, 'No se pudo retirar el docente.')[0]));
    }
    exit;
} elseif ($uri === '/estudios/retirar-directo') {
    $cedula = (int)($_GET['docente'] ?? 0);
    $res = retirar_estudio_realizado((int)($_GET['id'] ?? 0));
    $clave = $res['codigo'] === 200 ? 'exito' : 'error';
    $texto = $res['codigo'] === 200 ? 'Estudio retirado correctamente.' : errores_de_respuesta($res, 'No se pudo retirar el estudio.')[0];
    header('Location: /docentes/editar?cedula=' . $cedula . '&' . $clave . '=' . urlencode($texto));
    exit;

// ---------------------------------------------------------------------------
// ESTUDIOS / EVALUACIONES / EXPERIENCIAS / RECONOCIMIENTOS (despacho común)
// ---------------------------------------------------------------------------
} elseif (($entidad = entidad_simple($uri)) !== null) {
    [$cfg, $accion] = $entidad;
    $base = $cfg['base'];
    $etq  = $cfg['etiqueta'];

    if ($accion === 'lista') {
        $respuesta = $cfg['listar']();
        ${$cfg['var_lista']} = isset($respuesta['error']) ? [] : ($respuesta['datos'] ?? []);
        if (isset($respuesta['error'])) {
            $mensajeError = $respuesta['error'];
        }
        $mapaDocentes = mapa_docentes();
        require __DIR__ . '/vistas/' . $cfg['lista'] . '.php';
    } elseif ($accion === 'nuevo' || $accion === 'editar') {
        ${$cfg['var_form']} = [];
        if ($accion === 'editar') {
            ${$cfg['var_form']} = $cfg['obtener']((int)($_GET['id'] ?? 0));
            $actual = ${$cfg['var_form']};
            if (isset($actual['error']) || (isset($actual['estado']) && $actual['estado'] === 404)) {
                header('Location: ' . $base . '?error=' . urlencode("{$etq}: el registro seleccionado no existe."));
                exit;
            }
        }
        $errores = [];
        $docentes = listar_docentes()['datos'] ?? [];
        require __DIR__ . '/vistas/' . $cfg['form'] . '.php';
    } elseif ($accion === 'crear' && $metodo === 'POST') {
        $datos = $_POST;
        unset($datos['id']);
        $res = $cfg['crear'](tipar_campos($datos, $cfg['enteros'], $cfg['decimales'], $cfg['nulables']));
        if (in_array($res['codigo'], [200, 201], true)) {
            header('Location: ' . $base . '?exito=' . urlencode("{$etq} registrado correctamente."));
            exit;
        }
        ${$cfg['var_form']} = $_POST;
        $errores = errores_de_respuesta($res, "Error al registrar: {$etq}.");
        $docentes = listar_docentes()['datos'] ?? [];
        require __DIR__ . '/vistas/' . $cfg['form'] . '.php';
    } elseif ($accion === 'guardar-edicion' && $metodo === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $datos = $_POST;
        unset($datos['id'], $datos['accion']);
        if (($_POST['accion'] ?? 'reemplazar') === 'parcial') {
            $datos = array_filter($datos, fn($v) => $v !== '' && $v !== null);
            $res = $cfg['parcial']($id, tipar_campos($datos, $cfg['enteros'], $cfg['decimales']));
        } else {
            $res = $cfg['reemplazar']($id, tipar_campos($datos, $cfg['enteros'], $cfg['decimales'], $cfg['nulables']));
        }
        if ($res['codigo'] === 200) {
            header('Location: ' . $base . '?exito=' . urlencode("{$etq} modificado correctamente."));
            exit;
        }
        ${$cfg['var_form']} = $_POST;
        $errores = errores_de_respuesta($res, "Error al modificar: {$etq}.");
        $docentes = listar_docentes()['datos'] ?? [];
        require __DIR__ . '/vistas/' . $cfg['form'] . '.php';
    } elseif ($accion === 'retirar' && $metodo === 'POST') {
        $res = $cfg['retirar']((int)($_POST['id'] ?? 0));
        if ($res['codigo'] === 200) {
            header('Location: ' . $base . '?exito=' . urlencode("{$etq} retirado correctamente."));
        } else {
            header('Location: ' . $base . '?error=' . urlencode(errores_de_respuesta($res, "No se pudo retirar: {$etq}.")[0]));
        }
        exit;
    } else {
        http_response_code(404);
        require __DIR__ . '/vistas/no_encontrada.php';
    }

// ---------------------------------------------------------------------------
// v3: USUARIOS (solo admin; el guardia de arriba ya filtra el acceso)
// ---------------------------------------------------------------------------
} elseif ($uri === '/usuarios') {
    $respuesta = listar_usuarios();
    if (isset($respuesta['error']) || isset($respuesta['mensaje']) && !isset($respuesta['datos'])) {
        $mensajeError = $respuesta['error'] ?? $respuesta['mensaje'];
        $usuarios = [];
    } else {
        $usuarios = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_usuarios.php';
} elseif ($uri === '/usuarios/crear') {
    $usuario = [];
    $rolesDisponibles = listar_roles()['datos'] ?? [];
    if ($metodo === 'POST') {
        $datos = array_intersect_key($_POST, array_flip(['username', 'password', 'email', 'nombre_completo']));
        $res = crear_usuario($datos);
        if ($res['codigo'] === 201) {
            $nuevoId = (int)($res['cuerpo']['datos']['id'] ?? 0);
            foreach ((array)($_POST['rol_ids'] ?? []) as $rolId) {
                asignar_rol_usuario($nuevoId, (int)$rolId);
            }
            header('Location: /usuarios?exito=' . urlencode('Usuario creado correctamente.'));
            exit;
        }
        $usuario = $_POST;
        $mensajeError = implode(' ', errores_de_respuesta($res, 'Error al crear el usuario.'));
    }
    require __DIR__ . '/vistas/formulario_usuario.php';
} elseif ($uri === '/usuarios/editar') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    $rolesDisponibles = listar_roles()['datos'] ?? [];
    $usuario = obtener_usuario($id);
    if (isset($usuario['error']) || !isset($usuario['id'])) {
        header('Location: /usuarios?error=' . urlencode('El usuario seleccionado no existe.'));
        exit;
    }
    if ($metodo === 'POST') {
        $marcados = array_map('intval', (array)($_POST['rol_ids'] ?? []));
        $esUnoMismo = $id === (int)($_SESSION['usuario']['id'] ?? 0);
        $adminId = 0;
        foreach ($rolesDisponibles as $r) {
            if (strtolower((string)$r['nombre']) === 'admin') {
                $adminId = (int)$r['id'];
            }
        }
        if ($esUnoMismo && $adminId > 0 && !in_array($adminId, $marcados, true)) {
            $mensajeError = 'No puede quitarse a sí mismo el rol admin.';
        } else {
            $datos = array_intersect_key($_POST, array_flip(['email', 'nombre_completo', 'password']));
            if (trim((string)($datos['password'] ?? '')) === '') {
                unset($datos['password']);
            }
            $res = actualizar_usuario($id, $datos);
            if ($res['codigo'] === 200) {
                $actuales = array_map('intval', array_column(obtener_roles_usuario($id)['roles'] ?? [], 'id'));
                foreach (array_diff($marcados, $actuales) as $rolId) {
                    asignar_rol_usuario($id, $rolId);
                }
                foreach (array_diff($actuales, $marcados) as $rolId) {
                    desasignar_rol_usuario($id, $rolId);
                }
                header('Location: /usuarios?exito=' . urlencode('Usuario modificado correctamente.'));
                exit;
            }
            $mensajeError = implode(' ', errores_de_respuesta($res, 'Error al modificar el usuario.'));
        }
        $usuario = array_merge($usuario, $_POST);
    }
    require __DIR__ . '/vistas/formulario_usuario.php';
} elseif ($uri === '/usuarios/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)($_SESSION['usuario']['id'] ?? 0)) {
        header('Location: /usuarios?error=' . urlencode('No puede retirar su propia cuenta.'));
        exit;
    }
    $res = retirar_usuario($id);
    if ($res['codigo'] === 200) {
        header('Location: /usuarios?exito=' . urlencode('Usuario retirado correctamente.'));
    } else {
        header('Location: /usuarios?error=' . urlencode(errores_de_respuesta($res, 'No se pudo retirar el usuario.')[0]));
    }
    exit;

// ---------------------------------------------------------------------------
// v3: ROLES (solo admin)
// ---------------------------------------------------------------------------
} elseif ($uri === '/roles') {
    $respuesta = listar_roles();
    if (isset($respuesta['error']) || isset($respuesta['mensaje']) && !isset($respuesta['datos'])) {
        $mensajeError = $respuesta['error'] ?? $respuesta['mensaje'];
        $roles = [];
    } else {
        $roles = $respuesta['datos'] ?? [];
    }
    require __DIR__ . '/vistas/lista_roles.php';
} elseif ($uri === '/roles/crear') {
    $rol = [];
    if ($metodo === 'POST') {
        $res = crear_rol(array_intersect_key($_POST, array_flip(['nombre', 'descripcion'])));
        if ($res['codigo'] === 201) {
            header('Location: /roles?exito=' . urlencode('Rol creado correctamente.'));
            exit;
        }
        $rol = $_POST;
        $mensajeError = implode(' ', errores_de_respuesta($res, 'Error al crear el rol.'));
    }
    require __DIR__ . '/vistas/formulario_rol.php';
} elseif ($uri === '/roles/editar') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    $rol = obtener_rol($id);
    if (isset($rol['error']) || !isset($rol['id'])) {
        header('Location: /roles?error=' . urlencode('El rol seleccionado no existe.'));
        exit;
    }
    if ($metodo === 'POST') {
        $res = actualizar_rol($id, array_intersect_key($_POST, array_flip(['nombre', 'descripcion'])));
        if ($res['codigo'] === 200) {
            header('Location: /roles?exito=' . urlencode('Rol modificado correctamente.'));
            exit;
        }
        $rol = array_merge($rol, $_POST);
        $mensajeError = implode(' ', errores_de_respuesta($res, 'Error al modificar el rol.'));
    }
    require __DIR__ . '/vistas/formulario_rol.php';
} elseif ($uri === '/roles/retirar' && $metodo === 'POST') {
    $res = retirar_rol((int)($_POST['id'] ?? 0));
    if ($res['codigo'] === 200) {
        header('Location: /roles?exito=' . urlencode('Rol retirado correctamente.'));
    } else {
        header('Location: /roles?error=' . urlencode(errores_de_respuesta($res, 'No se pudo retirar el rol.')[0]));
    }
    exit;

} else {
    http_response_code(404);
    require __DIR__ . '/vistas/no_encontrada.php';
}

$contenido = ob_get_clean();
require __DIR__ . '/vistas/plantilla.php';