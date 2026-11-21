<?php
declare(strict_types=1);

/**
 * Cliente HTTP cURL para consumir la API de Gestión Profesoral.
 * No comparte código ni clases con la API; únicamente procesa datos JSON.
 */

function obtenerUrlApi(): string
{
    if (!getenv('API_BASE_URL')) {
        $posiblesEnv = [__DIR__ . '/../.env', __DIR__ . '/.env'];
        foreach ($posiblesEnv as $archivoEnv) {
            if (file_exists($archivoEnv)) {
                $lineas = @file($archivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($lineas as $linea) {
                    $linea = trim($linea);
                    if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) continue;
                    [$k, $v] = explode('=', $linea, 2);
                    $k = trim($k);
                    $v = trim($v);
                    if ($k === 'API_BASE_URL') {
                        putenv("$k=$v");
                        $_ENV[$k] = $v;
                        return $v;
                    }
                }
            }
        }
    }
    if (!empty($_SERVER['HTTP_HOST'])) {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        return rtrim($protocolo . $_SERVER['HTTP_HOST'], '/');
    }
    return getenv('API_BASE_URL') ?: 'http://api_gestion:8113';
}

function obtenerCabecerasHttp(bool $conJson = false): array
{
    $headers = [];
    if ($conJson) {
        $headers[] = 'Content-Type: application/json';
    }
    if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }
    return $headers;
}

function listar_programas(int $limite = 1000): array
{
    $url = obtenerUrlApi() . '/api/programa?limite=' . $limite;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
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
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}


// -----------------------------------------------------------------------------
// ALIAS DE COMPATIBILIDAD V1 / V2 (LÍNEAS DE INVESTIGACIÓN Y TÉRMINOS CLAVE)
// -----------------------------------------------------------------------------

function listar_lineas_investigacion(int $limite = 1000): array
{
    return listar_lineas($limite);
}

function obtener_linea_investigacion(int $id): array
{
    return obtener_linea($id);
}

function crear_linea_investigacion(array $datos): array
{
    return crear_linea($datos);
}

function reemplazar_linea_investigacion(int $id, array $datos): array
{
    return reemplazar_linea($id, $datos);
}

function actualizar_parcial_linea_investigacion(int $id, array $datos): array
{
    return actualizar_parcial_linea($id, $datos);
}

function retirar_linea_investigacion(int $id): array
{
    return retirar_linea($id);
}

function listar_terminos_clave(int $limite = 1000): array
{
    return listar_terminos($limite);
}

function obtener_termino_clave(string $termino): array
{
    return obtener_termino($termino);
}

function crear_termino_clave(array $datos): array
{
    return crear_termino($datos);
}

function reemplazar_termino_clave(string $termino, array $datos): array
{
    return reemplazar_termino($termino, $datos);
}

function actualizar_parcial_termino_clave(string $termino, array $datos): array
{
    return actualizar_parcial_termino($termino, $datos);
}

function retirar_termino_clave(string $termino): array
{
    return retirar_termino($termino);
}


// =============================================================================
// V2: TABLAS DEPENDIENTES Y ASOCIACIONES (PARTE COMPANERO)
// =============================================================================


// --- 1. DOCENTE - DEPARTAMENTO ---
function listar_docente_departamentos(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente-departamento?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'docente_departamento', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_docente_departamento(int $docente, int $departamento): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/docente-departamento/{$docente}/{$departamento}");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_docente_departamento(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/docente-departamento');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_docente_departamento(int $docente, int $departamento, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/docente-departamento/{$docente}/{$departamento}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_docente_departamento(int $docente, int $departamento, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/docente-departamento/{$docente}/{$departamento}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_docente_departamento(int $docente, int $departamento): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/docente-departamento/{$docente}/{$departamento}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// --- 2. INTERESES FUTUROS (TABLA PUENTE) ---
function listar_intereses_futuros(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/intereses-futuros?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'intereses_futuros', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function crear_interes_futuro(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/intereses-futuros');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_interes_futuro(int $docente, string $termino): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/intereses-futuros/{$docente}/" . rawurlencode($termino));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// --- 3. RED DOCENTE ---
function listar_redes_docentes(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red-docente?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'red_docente', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_red_docente(int $red, int $docente): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/red-docente/{$red}/{$docente}");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_red_docente(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/red-docente');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_red_docente(int $red, int $docente, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/red-docente/{$red}/{$docente}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_red_docente(int $red, int $docente, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/red-docente/{$red}/{$docente}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_red_docente(int $red, int $docente): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/red-docente/{$red}/{$docente}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// --- 4. ESTUDIO - ÁREA DE CONOCIMIENTO (TABLA PUENTE) ---
function listar_estudios_ac(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudio-ac?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'estudio_ac', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function crear_estudio_ac(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/estudio-ac');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_estudio_ac(int $estudio, string $area): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/estudio-ac/{$estudio}/" . rawurlencode($area));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// --- 5. APOYO PROFESORAL ---
function listar_apoyos_profesorales(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/apoyo-profesoral?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'apoyo_profesoral', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_apoyo_profesoral(int $estudios): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/apoyo-profesoral/{$estudios}");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_apoyo_profesoral(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/apoyo-profesoral');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_apoyo_profesoral(int $estudios, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/apoyo-profesoral/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_apoyo_profesoral(int $estudios, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/apoyo-profesoral/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_apoyo_profesoral(int $estudios): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/apoyo-profesoral/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// --- 6. BECA ---
function listar_becas(int $limite = 1000): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/beca?limite=' . $limite);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    if ($codigoHttp === 204) {
        return ['tabla' => 'beca', 'total' => 0, 'datos' => []];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_beca(int $estudios): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/beca/{$estudios}");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => obtenerCabecerasHttp(false)]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'Sin conexión con la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_beca(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/beca');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function reemplazar_beca(int $estudios, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/beca/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_parcial_beca(int $estudios, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/beca/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_beca(int $estudios): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/beca/{$estudios}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false)
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}


// =============================================================================
// V3: AUTENTICACIÓN, GESTIÓN DE USUARIOS Y ROLES (CLIENTE API)
// =============================================================================

function iniciar_sesion_api(string $username, string $password): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['username' => $username, 'password' => $password]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function obtener_sesion_api(): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/sesion');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function cerrar_sesion_api(): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/sesion');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// -----------------------------------------------------------------------------
// CRUD USUARIOS
// -----------------------------------------------------------------------------

function listar_usuarios(): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/usuario');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_usuario(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/usuario/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_usuario(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/usuario');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_usuario(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/usuario/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_usuario(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/usuario/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// -----------------------------------------------------------------------------
// CRUD ROLES
// -----------------------------------------------------------------------------

function listar_roles(): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/rol');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp === 0) {
        return ['error' => 'No se pudo conectar con el servidor de la API.'];
    }
    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida del servidor.'];
}

function obtener_rol(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/rol/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function crear_rol(array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/rol');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function actualizar_rol(int $id, array $datos): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/rol/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function retirar_rol(int $id): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/rol/{$id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

// -----------------------------------------------------------------------------
// ASIGNACIÓN DE ROLES
// -----------------------------------------------------------------------------

function listar_roles_usuarios(): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/rol-usuario');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function obtener_roles_usuario(int $usuarioId): array
{
    $ch = curl_init(obtenerUrlApi() . "/api/rol-usuario/{$usuarioId}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(false),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $datos = json_decode((string)$respuesta, true);
    return is_array($datos) ? $datos : ['error' => 'Respuesta no válida.'];
}

function asignar_rol_usuario(int $usuarioId, int $rolId): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/rol-usuario');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['usuario_id' => $usuarioId, 'rol_id' => $rolId]),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}

function desasignar_rol_usuario(int $usuarioId, int $rolId): array
{
    $ch = curl_init(obtenerUrlApi() . '/api/rol-usuario');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_POSTFIELDS     => json_encode(['usuario_id' => $usuarioId, 'rol_id' => $rolId]),
        CURLOPT_HTTPHEADER     => obtenerCabecerasHttp(true),
        CURLOPT_TIMEOUT        => 5
    ]);
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['codigo' => $codigoHttp, 'cuerpo' => json_decode((string)$respuesta, true) ?? []];
}
