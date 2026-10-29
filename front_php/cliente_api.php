<?php
declare(strict_types=1);

/**
 * Cliente HTTP cURL para consumir la API de Gestión Profesoral.
 * No comparte código ni clases con la API; únicamente procesa datos JSON.
 */

function obtenerUrlApi(): string
{
    return getenv('API_BASE_URL') ?: 'http://api_gestion:8113';
}

function listar_programas(int $limite = 1000): array
{
    $url = obtenerUrlApi() . '/api/programa?limite=' . $limite;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo establecer conexión con el servidor de la API.'];
    }

    if ($codigoHttp === 204) {
        return ['tabla' => 'programa', 'total' => 0, 'datos' => []];
    }

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_programa(int $id): array
{
    $url = obtenerUrlApi() . '/api/programa/' . $id;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_programa(array $datos): array
{
    $url = obtenerUrlApi() . '/api/programa';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$respuesta, true) ?? [];
    return ['codigo' => $codigoHttp, 'cuerpo' => $json];
}

function reemplazar_programa(int $id, array $datos): array
{
    $url = obtenerUrlApi() . '/api/programa/' . $id;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$respuesta, true) ?? [];
    return ['codigo' => $codigoHttp, 'cuerpo' => $json];
}

function actualizar_parcial_programa(int $id, array $datos): array
{
    $url = obtenerUrlApi() . '/api/programa/' . $id;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$respuesta, true) ?? [];
    return ['codigo' => $codigoHttp, 'cuerpo' => $json];
}

function retirar_programa(int $id): array
{
    $url = obtenerUrlApi() . '/api/programa/' . $id;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);

    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$respuesta, true) ?? [];
    return ['codigo' => $codigoHttp, 'cuerpo' => $json];
}

// ───────────── RED ─────────────

function listar_redes(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo establecer conexión con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'red', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_red(int $idr): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red/' . $idr);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_red(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_red(int $idr, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red/' . $idr);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_red(int $idr, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red/' . $idr);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_red(int $idr): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red/' . $idr);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// ───────────── LÍNEA DE INVESTIGACIÓN ─────────────

function listar_lineas(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo establecer conexión con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'linea_investigacion', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_linea(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion/' . $id);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_linea(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_linea(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_linea(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_linea(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/linea-investigacion/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}


// ───────────── TÉRMINO CLAVE ─────────────

function listar_terminos(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo establecer conexión con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'termino_clave', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_termino(string $termino): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave/' . rawurlencode($termino));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_termino(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_termino(string $termino, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave/' . rawurlencode($termino));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_termino(string $termino, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave/' . rawurlencode($termino));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_termino(string $termino): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/termino-clave/' . rawurlencode($termino));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// ───────────── ÁREA DE CONOCIMIENTO ─────────────

function listar_areas(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo establecer conexión con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'area_conocimiento', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_area(string $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento/' . rawurlencode($id));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_area(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_area(string $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento/' . rawurlencode($id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_area(string $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento/' . rawurlencode($id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_area(string $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/area-conocimiento/' . rawurlencode($id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// =============================================================================
// FUNCIONES CLIENTE V2: DOCENTE (MAESTRO)
// =============================================================================

function listar_docentes(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente?limite=' . $limite);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API de docentes.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'docente', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_docente(int $cedula): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente/' . $cedula);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_docente(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_docente(int $cedula, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente/' . $cedula);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_docente(int $cedula, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente/' . $cedula);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_docente(int $cedula): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente/' . $cedula);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// =============================================================================
// FUNCIONES CLIENTE V2: ESTUDIOS REALIZADOS (DETALLE)
// =============================================================================

function listar_estudios_realizados(int $limite = 1000, ?int $docente = null): array
{
    $url = obtenerUrlApi() . '/api/estudios-realizados?limite=' . $limite;
    if ($docente !== null) {
        $url .= '&docente=' . $docente;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API de estudios.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'estudios_realizados', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_estudio_realizado(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudios-realizados/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_estudio_realizado(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudios-realizados');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_estudio_realizado(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudios-realizados/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_estudio_realizado(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudios-realizados/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_estudio_realizado(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudios-realizados/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// =============================================================================
// FUNCIONES CLIENTE V2: EVALUACION DOCENTE
// =============================================================================

function listar_evaluaciones_docente(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente?limite=' . $limite);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API de evaluaciones.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'evaluacion_docente', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_evaluacion_docente(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_evaluacion_docente(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_evaluacion_docente(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_evaluacion_docente(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_evaluacion_docente(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/evaluacion-docente/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// =============================================================================
// FUNCIONES CLIENTE V2: EXPERIENCIA (TABLA EXPERIECIA)
// =============================================================================

function listar_experiencias(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia?limite=' . $limite);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API de experiencia.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'experiecia', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_experiencia(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_experiencia(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_experiencia(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_experiencia(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_experiencia(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/experiencia/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// =============================================================================
// FUNCIONES CLIENTE V2: RECONOCIMIENTO
// =============================================================================

function listar_reconocimientos(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento?limite=' . $limite);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API de reconocimientos.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'reconocimiento', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_reconocimiento(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_reconocimiento(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_reconocimiento(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_reconocimiento(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_reconocimiento(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/reconocimiento/' . $id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}
