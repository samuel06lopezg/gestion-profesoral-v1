<?php
/**
 * Guardia de seguridad y control de acceso (Middleware RBAC).
 *
 * Aplica los dos controles de seguridad HTTP en cumplimiento de la especificación v3:
 * - 401 Unauthorized: El cliente no se ha autenticado o el token es inválido/vencido.
 * - 403 Forbidden: El cliente se ha autenticado pero su rol no tiene privilegios para la operación.
 */

declare(strict_types=1);

require_once __DIR__ . '/jwt.php';

/**
 * Emite una respuesta de error formalizada en JSON y finaliza inmediatamente la ejecución.
 */
function responderYSalir(int $estado, string $mensaje, string $detalle = ''): never
{
    http_response_code($estado);
    if ($estado === 401) {
        header('WWW-Authenticate: Bearer');
    }
    header('Content-Type: application/json; charset=utf-8');

    $cuerpo = [
        'estado'  => $estado,
        'mensaje' => $mensaje,
    ];
    if ($detalle !== '') {
        $cuerpo['detalle'] = $detalle;
    }

    echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Extrae el token portador (Bearer Token) del encabezado HTTP Authorization.
 */
function tokenDeLaCabecera(): ?string
{
    $cabeceras = function_exists('getallheaders') ? getallheaders() : [];
    $valor = '';

    foreach ($cabeceras as $nombre => $contenido) {
        if (strtolower((string) $nombre) === 'authorization') {
            $valor = (string) $contenido;
            break;
        }
    }

    if ($valor === '') {
        $valor = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    }

    if (!str_starts_with(strtolower($valor), 'bearer ')) {
        return null;
    }

    return trim(substr($valor, 7));
}

/**
 * Exige una sesión autenticada con JWT válido. Retorna el payload del token si es exitoso,
 * o aborta con 401 Unauthorized si falta o es inválido.
 *
 * @return array{username: string, roles: string[], exp: int}
 */
function exigirSesion(): array
{
    $token = tokenDeLaCabecera();
    if ($token === null) {
        responderYSalir(
            401,
            'No hay una sesion valida. Inicie sesion.',
            'Falta la cabecera Authorization: Bearer <token>.'
        );
    }

    try {
        $datos = jwt_leer($token);
    } catch (InvalidArgumentException $e) {
        responderYSalir(
            401,
            'No hay una sesion valida. Inicie sesion.',
            $e->getMessage()
        );
    }

    $username = (string) ($datos['sub'] ?? '');
    if ($username === '') {
        responderYSalir(
            401,
            'No hay una sesion valida. Inicie sesion.',
            'El token no contiene la identidad del usuario.'
        );
    }

    return [
        'username' => $username,
        'roles'    => (array) ($datos['roles'] ?? []),
        'exp'      => (int) ($datos['exp'] ?? 0),
    ];
}

/**
 * Exige que el usuario autenticado posea al menos uno de los roles autorizados.
 * Si no cumple, aborta con 403 Forbidden.
 *
 * @param string|string[] $rolesPermitidos
 * @return array{username: string, roles: string[], exp: int}
 */
function exigirRol(string|array $rolesPermitidos): array
{
    $quien = exigirSesion();
    $permitidos = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];

    $tienePermiso = false;
    foreach ($permitidos as $rolRequerido) {
        if (in_array(strtolower($rolRequerido), array_map('strtolower', $quien['roles']), true)) {
            $tienePermiso = true;
            break;
        }
    }

    if (!$tienePermiso) {
        responderYSalir(
            403,
            'Su rol no tiene permiso para esta operacion.',
            'El recurso exige uno de los siguientes roles: ' . implode(', ', $permitidos) . '.'
        );
    }

    return $quien;
}

/**
 * Guardia de operaciones de escritura (POST, PUT, DELETE).
 * Deniega el acceso con 403 Forbidden si el usuario tiene exclusivamente el rol 'consulta'.
 *
 * @return array{username: string, roles: string[], exp: int}
 */
function exigirEscritura(): array
{
    $quien = exigirSesion();
    $rolesUsuario = array_map('strtolower', $quien['roles']);

    // Si tiene admin o docente, puede escribir
    if (in_array('admin', $rolesUsuario, true) || in_array('docente', $rolesUsuario, true)) {
        return $quien;
    }

    // Si solo tiene consulta o ningún rol de escritura, se bloquea
    responderYSalir(
        403,
        'Su rol no tiene permiso para esta operacion.',
        'El rol consulta solo tiene permisos de lectura (GET).'
    );
}
