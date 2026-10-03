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