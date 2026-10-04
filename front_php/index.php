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

ob_start();

if ($uri === '/' || $uri === '') {
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
} else {
    http_response_code(404);
    require __DIR__ . '/vistas/no_encontrada.php';
}

$contenido = ob_get_clean();
require __DIR__ . '/vistas/plantilla.php';