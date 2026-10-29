<?php
declare(strict_types=1);

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

// Función auxiliar para construir mapa de docentes: [cedula => "Nombre Apellido"]
function obtenerMapaDocentes(): array {
    $resp = listar_docentes();
    $mapa = [];
    if (!isset($resp['error']) && !empty($resp['datos'])) {
        foreach ($resp['datos'] as $d) {
            $mapa[(int)$d['cedula']] = $d['nombres'] . ' ' . $d['apellidos'];
        }
    }
    return $mapa;
}

// Función auxiliar para empacar docente y sus renglones de estudios (Maestro-Detalle)
function empacarDatosDocente(array $post): array {
    $linea = ($post['linea_investigacion_principal'] ?? '') === '' ? null : (int)$post['linea_investigacion_principal'];
    $cat = ($post['cat_minciencia'] ?? '') === '' ? null : (string)$post['cat_minciencia'];

    $docente = [
        'cedula'                        => (int)($post['cedula'] ?? 0),
        'nombres'                       => (string)($post['nombres'] ?? ''),
        'apellidos'                     => (string)($post['apellidos'] ?? ''),
        'genero'                        => (string)($post['genero'] ?? ''),
        'cargo'                         => (string)($post['cargo'] ?? ''),
        'fecha_nacimiento'              => (string)($post['fecha_nacimiento'] ?? ''),
        'correo'                        => (string)($post['correo'] ?? ''),
        'telefono'                      => (string)($post['telefono'] ?? ''),
        'url_cvlac'                     => (string)($post['url_cvlac'] ?? ''),
        'fecha_actualizacion'           => (string)($post['fecha_actualizacion'] ?? ''),
        'escalafon'                     => (string)($post['escalafon'] ?? ''),
        'perfil'                        => (string)($post['perfil'] ?? ''),
        'cat_minciencia'                => $cat,
        'conv_minciencia'               => (string)($post['conv_minciencia'] ?? ''),
        'nacionalidaad'                 => (string)($post['nacionalidaad'] ?? ''),
        'linea_investigacion_principal' => $linea,
        'estudios'                      => []
    ];

    if (!empty($post['detalle_titulo']) && is_array($post['detalle_titulo'])) {
        $titulos       = $post['detalle_titulo'];
        $universidades = $post['detalle_universidad'] ?? [];
        $tipos         = $post['detalle_tipo'] ?? [];
        $fechas        = $post['detalle_fecha'] ?? [];
        $ciudades      = $post['detalle_ciudad'] ?? [];
        $paises        = $post['detalle_pais'] ?? [];
        $metodologias  = $post['detalle_metodologia'] ?? [];
        $acreditadas   = $post['detalle_ins_acreditada'] ?? [];
        $perfiles      = $post['detalle_perfil_egresado'] ?? [];

        foreach ($titulos as $i => $titulo) {
            $t = trim((string)$titulo);
            if ($t === '') {
                continue;
            }
            $docente['estudios'][] = [
                'titulo'          => $t,
                'universidad'     => (string)($universidades[$i] ?? ''),
                'tipo'            => (string)($tipos[$i] ?? 'Pregrado'),
                'fecha'           => (string)($fechas[$i] ?? date('Y-m-d')),
                'ciudad'          => (string)($ciudades[$i] ?? ''),
                'pais'            => (string)($paises[$i] ?? 'Colombia'),
                'metodologia'     => (string)($metodologias[$i] ?? 'Presencial'),
                'ins_acreditada'  => (int)($acreditadas[$i] ?? 1),
                'perfil_egresado' => (string)($perfiles[$i] ?? '')
            ];
        }
    }

    return $docente;
}

ob_start();

// =============================================================================
// ENRUTAMIENTO GENERAL
// =============================================================================

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/vistas/inicio.php';

// -----------------------------------------------------------------------------
// V1: PROGRAMAS ACADÉMICOS
// -----------------------------------------------------------------------------
} elseif ($uri === '/programas') {
    $respuesta = listar_programas();
    $programas = isset($respuesta['datos']) ? $respuesta['datos'] : [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista.php';
} elseif ($uri === '/programas/nuevo') {
    $programa = [];
    $errores  = [];
    require __DIR__ . '/vistas/formulario.php';
} elseif ($uri === '/programas/crear' && $metodo === 'POST') {
    $res = crear_programa($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /programas?exito=' . urlencode('Programa creado correctamente.'));
        exit;
    } else {
        $programa = $_POST;
        $errores  = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el programa.'];
        require __DIR__ . '/vistas/formulario.php';
    }
} elseif ($uri === '/programas/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $programa = obtener_programa($id);
    if (isset($programa['error']) || (isset($programa['estado']) && $programa['estado'] === 404)) {
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
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['id'], $datos['accion']);
        $res = actualizar_parcial_programa($id, $datos);
    }
    if ($res['codigo'] === 200) {
        header('Location: /programas?exito=' . urlencode('Programa modificado correctamente.'));
        exit;
    } else {
        $errores  = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al modificar el programa.'];
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

// -----------------------------------------------------------------------------
// V1: REDES ACADÉMICAS
// -----------------------------------------------------------------------------
} elseif ($uri === '/redes') {
    $respuesta = listar_redes();
    $redes = $respuesta['datos'] ?? [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
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
        header('Location: /redes?exito=' . urlencode('Red académica creada exitosamente.'));
        exit;
    } else {
        $red = $_POST;
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
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['idr'], $datos['accion']);
        $res = actualizar_parcial_red($idr, $datos);
    }
    if ($res['codigo'] === 200) {
        header('Location: /redes?exito=' . urlencode('Red académica modificada correctamente.'));
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
        header('Location: /redes?exito=' . urlencode('Red académica retirada con éxito.'));
    } else {
        header('Location: /redes?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la red.'));
    }
    exit;

// -----------------------------------------------------------------------------
// V1: LÍNEAS DE INVESTIGACIÓN
// -----------------------------------------------------------------------------
} elseif ($uri === '/lineas') {
    $respuesta = listar_lineas_investigacion();
    $lineas = $respuesta['datos'] ?? [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_lineas.php';
} elseif ($uri === '/lineas/nueva') {
    $linea = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_linea.php';
} elseif ($uri === '/lineas/crear' && $metodo === 'POST') {
    $res = crear_linea_investigacion($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /lineas?exito=' . urlencode('Línea de investigación creada exitosamente.'));
        exit;
    } else {
        $linea = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear la línea.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_linea.php';
    }
} elseif ($uri === '/lineas/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $linea = obtener_linea_investigacion($id);
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
        $res = reemplazar_linea_investigacion($id, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['id'], $datos['accion']);
        $res = actualizar_parcial_linea_investigacion($id, $datos);
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
    $res = retirar_linea_investigacion($id);
    if ($res['codigo'] === 200) {
        header('Location: /lineas?exito=' . urlencode('Línea de investigación retirada con éxito.'));
    } else {
        header('Location: /lineas?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la línea.'));
    }
    exit;

// -----------------------------------------------------------------------------
// V1: TÉRMINOS CLAVE
// -----------------------------------------------------------------------------
} elseif ($uri === '/terminos') {
    $respuesta = listar_terminos_clave();
    $terminos = $respuesta['datos'] ?? [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_terminos.php';
} elseif ($uri === '/terminos/nuevo') {
    $termino = [];
    $errores = [];
    $esEdicion = false;
    require __DIR__ . '/vistas/formulario_termino.php';
} elseif ($uri === '/terminos/crear' && $metodo === 'POST') {
    $res = crear_termino_clave($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /terminos?exito=' . urlencode('Término clave creado exitosamente.'));
        exit;
    } else {
        $termino = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el término clave.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_termino.php';
    }
} elseif ($uri === '/terminos/editar') {
    $terminoTxt = (string)($_GET['termino'] ?? '');
    $termino = obtener_termino_clave($terminoTxt);
    if (isset($termino['error']) || (isset($termino['estado']) && $termino['estado'] === 404)) {
        header('Location: /terminos?error=' . urlencode('El término clave no existe.'));
        exit;
    }
    $errores = [];
    $esEdicion = true;
    require __DIR__ . '/vistas/formulario_termino.php';
} elseif ($uri === '/terminos/guardar-edicion' && $metodo === 'POST') {
    $terminoTxt = (string)($_POST['termino'] ?? '');
    $accion = $_POST['accion'] ?? 'reemplazar';
    if ($accion === 'reemplazar') {
        $res = reemplazar_termino_clave($terminoTxt, $_POST);
    } else {
        $datos = array_filter($_POST, fn($v) => $v !== '' && $v !== null);
        unset($datos['termino'], $datos['accion']);
        $res = actualizar_parcial_termino_clave($terminoTxt, $datos);
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
    $terminoTxt = (string)($_POST['termino'] ?? '');
    $res = retirar_termino_clave($terminoTxt);
    if ($res['codigo'] === 200) {
        header('Location: /terminos?exito=' . urlencode('Término clave retirado con éxito.'));
    } else {
        header('Location: /terminos?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el término.'));
    }
    exit;

// -----------------------------------------------------------------------------
// V1: ÁREAS DE CONOCIMIENTO
// -----------------------------------------------------------------------------
} elseif ($uri === '/areas') {
    $respuesta = listar_areas();
    $areas = $respuesta['datos'] ?? [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
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
        header('Location: /areas?exito=' . urlencode('Área de conocimiento creada exitosamente.'));
        exit;
    } else {
        $area = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al crear el área.'];
        $esEdicion = false;
        require __DIR__ . '/vistas/formulario_area.php';
    }
} elseif ($uri === '/areas/editar') {
    $id = (string)($_GET['id'] ?? '');
    $area = obtener_area($id);
    if (isset($area['error']) || (isset($area['estado']) && $area['estado'] === 404)) {
        header('Location: /areas?error=' . urlencode('El área de conocimiento no existe.'));
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

// =============================================================================
// V2: DOCENTES (MAESTRO)
// =============================================================================
} elseif ($uri === '/docentes') {
    $respuesta = listar_docentes();
    $docentes = $respuesta['datos'] ?? [];
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_docentes.php';
} elseif ($uri === '/docentes/nuevo') {
    $docente = [];
    $errores = [];
    $esNuevo = true;
    $lineasResp = listar_lineas_investigacion();
    $lineasInvestigacion = $lineasResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/crear' && $metodo === 'POST') {
    $payload = empacarDatosDocente($_POST);
    $res = crear_docente($payload);

    if ($res['codigo'] === 200) {
        header('Location: /docentes?exito=' . urlencode('Docente y estudios registrados exitosamente en un único envío.'));
        exit;
    } else {
        $docente = $payload;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar el docente.'];
        $esNuevo = true;
        $lineasResp = listar_lineas_investigacion();
        $lineasInvestigacion = $lineasResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_docente.php';
    }
} elseif ($uri === '/docentes/editar') {
    $cedula = (int)($_GET['cedula'] ?? 0);
    $docente = obtener_docente($cedula);

    if (isset($docente['error']) || (isset($docente['estado']) && $docente['estado'] === 404)) {
        header('Location: /docentes?error=' . urlencode('El docente seleccionado no existe.'));
        exit;
    }
    $errores = [];
    $esNuevo = false;
    $lineasResp = listar_lineas_investigacion();
    $lineasInvestigacion = $lineasResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_docente.php';
} elseif ($uri === '/docentes/guardar-edicion' && $metodo === 'POST') {
    $cedula = (int)($_POST['cedula'] ?? 0);
    $payload = empacarDatosDocente($_POST);
    $res = reemplazar_docente($cedula, $payload);

    // Si se enviaron renglones nuevos de estudios durante la edición, guardarlos
    if (!empty($payload['estudios'])) {
        foreach ($payload['estudios'] as $nuevoEstudio) {
            $nuevoEstudio['docente'] = $cedula;
            crear_estudio_realizado($nuevoEstudio);
        }
    }

    if ($res['codigo'] === 200) {
        header('Location: /docentes?exito=' . urlencode('Docente y estudios actualizados correctamente.'));
        exit;
    } else {
        $docente = $payload;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al actualizar el docente.'];
        $esNuevo = false;
        $lineasResp = listar_lineas_investigacion();
        $lineasInvestigacion = $lineasResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_docente.php';
    }
} elseif ($uri === '/docentes/retirar' && $metodo === 'POST') {
    $cedula = (int)($_POST['cedula'] ?? 0);
    $res = retirar_docente($cedula);

    if ($res['codigo'] === 200) {
        header('Location: /docentes?exito=' . urlencode('Docente retirado con éxito.'));
    } else {
        // En caso de 409 (tiene estudios registrados u otro conflicto)
        $msg = $res['cuerpo']['mensaje'] ?? 'No se pudo retirar el docente.';
        header('Location: /docentes?error=' . urlencode($msg));
    }
    exit;

// =============================================================================
// V2: ESTUDIOS REALIZADOS (DETALLE)
// =============================================================================
} elseif ($uri === '/estudios') {
    $respuesta = listar_estudios_realizados();
    $estudios = $respuesta['datos'] ?? [];
    $mapaDocentes = obtenerMapaDocentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_estudios.php';
} elseif ($uri === '/estudios/nuevo') {
    $estudio = [];
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_estudio.php';
} elseif ($uri === '/estudios/crear' && $metodo === 'POST') {
    $res = crear_estudio_realizado($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /estudios?exito=' . urlencode('Estudio realizado registrado correctamente.'));
        exit;
    } else {
        $estudio = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar el estudio.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_estudio.php';
    }
} elseif ($uri === '/estudios/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $estudio = obtener_estudio_realizado($id);
    if (isset($estudio['error']) || (isset($estudio['estado']) && $estudio['estado'] === 404)) {
        header('Location: /estudios?error=' . urlencode('El estudio no existe.'));
        exit;
    }
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_estudio.php';
} elseif ($uri === '/estudios/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = reemplazar_estudio_realizado($id, $_POST);
    if ($res['codigo'] === 200) {
        header('Location: /estudios?exito=' . urlencode('Estudio actualizado correctamente.'));
        exit;
    } else {
        $estudio = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al actualizar el estudio.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_estudio.php';
    }
} elseif ($uri === '/estudios/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_estudio_realizado($id);
    if ($res['codigo'] === 200) {
        header('Location: /estudios?exito=' . urlencode('Estudio retirado con éxito.'));
    } else {
        header('Location: /estudios?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el estudio.'));
    }
    exit;
} elseif ($uri === '/estudios/retirar-directo') {
    $id = (int)($_GET['id'] ?? 0);
    $docenteCedula = (int)($_GET['docente'] ?? 0);
    retirar_estudio_realizado($id);
    header('Location: /docentes/editar?cedula=' . $docenteCedula . '&exito=' . urlencode('Estudio retirado.'));
    exit;

// =============================================================================
// V2: EVALUACION DOCENTE
// =============================================================================
} elseif ($uri === '/evaluaciones') {
    $respuesta = listar_evaluaciones_docente();
    $evaluaciones = $respuesta['datos'] ?? [];
    $mapaDocentes = obtenerMapaDocentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_evaluaciones.php';
} elseif ($uri === '/evaluaciones/nueva') {
    $evaluacion = [];
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_evaluacion.php';
} elseif ($uri === '/evaluaciones/crear' && $metodo === 'POST') {
    $res = crear_evaluacion_docente($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /evaluaciones?exito=' . urlencode('Evaluación registrada correctamente.'));
        exit;
    } else {
        $evaluacion = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar la evaluación.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_evaluacion.php';
    }
} elseif ($uri === '/evaluaciones/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $evaluacion = obtener_evaluacion_docente($id);
    if (isset($evaluacion['error']) || (isset($evaluacion['estado']) && $evaluacion['estado'] === 404)) {
        header('Location: /evaluaciones?error=' . urlencode('La evaluación no existe.'));
        exit;
    }
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_evaluacion.php';
} elseif ($uri === '/evaluaciones/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = reemplazar_evaluacion_docente($id, $_POST);
    if ($res['codigo'] === 200) {
        header('Location: /evaluaciones?exito=' . urlencode('Evaluación actualizada correctamente.'));
        exit;
    } else {
        $evaluacion = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al actualizar la evaluación.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_evaluacion.php';
    }
} elseif ($uri === '/evaluaciones/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_evaluacion_docente($id);
    if ($res['codigo'] === 200) {
        header('Location: /evaluaciones?exito=' . urlencode('Evaluación retirada con éxito.'));
    } else {
        header('Location: /evaluaciones?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la evaluación.'));
    }
    exit;

// =============================================================================
// V2: EXPERIENCIA (TABLA EXPERIECIA)
// =============================================================================
} elseif ($uri === '/experiencias') {
    $respuesta = listar_experiencias();
    $experiencias = $respuesta['datos'] ?? [];
    $mapaDocentes = obtenerMapaDocentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_experiencias.php';
} elseif ($uri === '/experiencias/nueva') {
    $experiencia = [];
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_experiencia.php';
} elseif ($uri === '/experiencias/crear' && $metodo === 'POST') {
    $res = crear_experiencia($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /experiencias?exito=' . urlencode('Experiencia registrada correctamente.'));
        exit;
    } else {
        $experiencia = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar la experiencia.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_experiencia.php';
    }
} elseif ($uri === '/experiencias/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $experiencia = obtener_experiencia($id);
    if (isset($experiencia['error']) || (isset($experiencia['estado']) && $experiencia['estado'] === 404)) {
        header('Location: /experiencias?error=' . urlencode('La experiencia no existe.'));
        exit;
    }
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_experiencia.php';
} elseif ($uri === '/experiencias/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = reemplazar_experiencia($id, $_POST);
    if ($res['codigo'] === 200) {
        header('Location: /experiencias?exito=' . urlencode('Experiencia actualizada correctamente.'));
        exit;
    } else {
        $experiencia = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al actualizar la experiencia.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_experiencia.php';
    }
} elseif ($uri === '/experiencias/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_experiencia($id);
    if ($res['codigo'] === 200) {
        header('Location: /experiencias?exito=' . urlencode('Experiencia retirada con éxito.'));
    } else {
        header('Location: /experiencias?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar la experiencia.'));
    }
    exit;

// =============================================================================
// V2: RECONOCIMIENTO
// =============================================================================
} elseif ($uri === '/reconocimientos') {
    $respuesta = listar_reconocimientos();
    $reconocimientos = $respuesta['datos'] ?? [];
    $mapaDocentes = obtenerMapaDocentes();
    if (isset($respuesta['error'])) {
        $mensajeError = $respuesta['error'];
    }
    require __DIR__ . '/vistas/lista_reconocimientos.php';
} elseif ($uri === '/reconocimientos/nuevo') {
    $reconocimiento = [];
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_reconocimiento.php';
} elseif ($uri === '/reconocimientos/crear' && $metodo === 'POST') {
    $res = crear_reconocimiento($_POST);
    if ($res['codigo'] === 200) {
        header('Location: /reconocimientos?exito=' . urlencode('Reconocimiento registrado correctamente.'));
        exit;
    } else {
        $reconocimiento = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al registrar el reconocimiento.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_reconocimiento.php';
    }
} elseif ($uri === '/reconocimientos/editar') {
    $id = (int)($_GET['id'] ?? 0);
    $reconocimiento = obtener_reconocimiento($id);
    if (isset($reconocimiento['error']) || (isset($reconocimiento['estado']) && $reconocimiento['estado'] === 404)) {
        header('Location: /reconocimientos?error=' . urlencode('El reconocimiento no existe.'));
        exit;
    }
    $errores = [];
    $docentesResp = listar_docentes();
    $docentes = $docentesResp['datos'] ?? [];
    require __DIR__ . '/vistas/formulario_reconocimiento.php';
} elseif ($uri === '/reconocimientos/guardar-edicion' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = reemplazar_reconocimiento($id, $_POST);
    if ($res['codigo'] === 200) {
        header('Location: /reconocimientos?exito=' . urlencode('Reconocimiento actualizado correctamente.'));
        exit;
    } else {
        $reconocimiento = $_POST;
        $errores = $res['cuerpo']['errores'] ?? [$res['cuerpo']['mensaje'] ?? 'Error al actualizar el reconocimiento.'];
        $docentesResp = listar_docentes();
        $docentes = $docentesResp['datos'] ?? [];
        require __DIR__ . '/vistas/formulario_reconocimiento.php';
    }
} elseif ($uri === '/reconocimientos/retirar' && $metodo === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $res = retirar_reconocimiento($id);
    if ($res['codigo'] === 200) {
        header('Location: /reconocimientos?exito=' . urlencode('Reconocimiento retirado con éxito.'));
    } else {
        header('Location: /reconocimientos?error=' . urlencode($res['cuerpo']['mensaje'] ?? 'No se pudo retirar el reconocimiento.'));
    }
    exit;

} else {
    http_response_code(404);
    require __DIR__ . '/vistas/no_encontrada.php';
}

$contenido = ob_get_clean();
require __DIR__ . '/vistas/plantilla.php';
