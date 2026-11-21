<?php
/**
 * Generación y verificación artesanal de JSON Web Tokens (JWT).
 *
 * Implementado en PHP 8.3 puro, sin Composer ni librerías de terceros,
 * en cumplimiento del Artículo 2 de la Constitución del Proyecto.
 *
 * Algoritmo de firma: HMAC-SHA256 (HS256) con codificación Base64 URL-safe (RFC 7515).
 */

declare(strict_types=1);

/**
 * Obtiene la clave secreta de firma desde el entorno del contenedor (Artículo 9:
 * cero secretos en el código). Sin clave configurada se aborta: un valor por defecto
 * conocido permitiría a cualquiera forjar tokens de administrador.
 */
function jwt_clave(): string
{
    $clave = (string) getenv('JWT_CLAVE');
    if ($clave === '') {
        throw new RuntimeException('La variable de entorno JWT_CLAVE no esta configurada.');
    }
    return $clave;
}

/**
 * Retorna la duración de vigencia del token en minutos (por defecto 120 minutos).
 */
function jwt_minutos(): int
{
    $minutos = (int) getenv('JWT_MINUTOS');
    return $minutos > 0 ? $minutos : 120;
}

/**
 * Codifica una cadena en Base64 URL-safe (sin +, /, ni relleno =).
 */
function jwt_base64_codificar(string $texto): string
{
    return rtrim(strtr(base64_encode($texto), '+/', '-_'), '=');
}

/**
 * Decodifica una cadena Base64 URL-safe restituyendo el relleno correspondiente.
 */
function jwt_base64_decodificar(string $texto): string
{
    $texto = strtr($texto, '-_', '+/');
    $relleno = strlen($texto) % 4;
    if ($relleno > 0) {
        $texto .= str_repeat('=', 4 - $relleno);
    }
    return (string) base64_decode($texto, true);
}

/**
 * Genera y firma un token JWT con la información del usuario y sus roles.
 *
 * @param string   $username Nombre de usuario (subject)
 * @param string[] $roles    Lista de nombres de roles asociados
 * @return array{0: string, 1: int} Retorna arreglo con el token y el timestamp de expiración
 */
function jwt_firmar(string $username, array $roles): array
{
    $expira = time() + (jwt_minutos() * 60);

    $encabezado = jwt_base64_codificar(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT',
    ], JSON_UNESCAPED_UNICODE));

    $carga = jwt_base64_codificar(json_encode([
        'sub'   => $username,
        'roles' => $roles,
        'iat'   => time(),
        'exp'   => $expira,
    ], JSON_UNESCAPED_UNICODE));

    $firma = jwt_base64_codificar(
        hash_hmac('sha256', "{$encabezado}.{$carga}", jwt_clave(), true)
    );

    return ["{$encabezado}.{$carga}.{$firma}", $expira];
}

/**
 * Decodifica y valida un token JWT comprobando formato, firma HMAC y vencimiento.
 *
 * @throws InvalidArgumentException Si el token es inválido, adulterado o ha expirado.
 */
function jwt_leer(string $token): array
{
    $partes = explode('.', $token);
    if (count($partes) !== 3) {
        throw new InvalidArgumentException('El token no posee tres segmentos validos.');
    }

    [$encabezado, $carga, $firma] = $partes;

    // Calcular la firma esperada con la clave del servidor
    $esperada = jwt_base64_codificar(
        hash_hmac('sha256', "{$encabezado}.{$carga}", jwt_clave(), true)
    );

    // Comparación en tiempo constante para mitigar ataques de temporización (timing attacks)
    if (!hash_equals($esperada, $firma)) {
        throw new InvalidArgumentException('La firma del token no es valida o fue adulterada.');
    }

    $datos = json_decode(jwt_base64_decodificar($carga), true);
    if (!is_array($datos)) {
        throw new InvalidArgumentException('La carga del token no es un JSON estructurado.');
    }

    if (!isset($datos['exp']) || (int) $datos['exp'] < time()) {
        throw new InvalidArgumentException('El token ha expirado. Inicie sesion nuevamente.');
    }

    return $datos;
}
