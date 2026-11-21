<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Servicios\IServicioSesion;
use Throwable;

class ControladorLogin
{
    public function __construct(
        private readonly IServicioSesion $servicio
    ) {
    }

    /**
     * POST /api/login (Público)
     */
    public function entrar(array $body): void
    {
        $errores = [];
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!is_string($username) || trim($username) === '') {
            $errores[] = 'El campo username es obligatorio.';
        }
        if (!is_string($password) || $password === '') {
            $errores[] = 'El campo password es obligatorio.';
        }

        if (!empty($errores)) {
            $this->responderJson(422, [
                'estado'  => 422,
                'mensaje' => 'Datos de entrada invalidos.',
                'errores' => $errores,
            ]);
            return;
        }

        try {
            $sesion = $this->servicio->entrar((string) $username, (string) $password);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno en la autenticacion.',
                'detalle' => $e->getMessage(),
            ]);
            return;
        }

        if ($sesion === null) {
            // Mensaje uniforme que previene enumeración de usuarios
            $this->responderJson(401, [
                'estado'  => 401,
                'mensaje' => 'El usuario o la contrasena no son correctos.',
            ]);
            return;
        }

        $this->responderJson(200, [
            'estado'  => 200,
            'mensaje' => 'Inicio de sesion exitoso.',
            'token'   => $sesion['token'],
            'expira'  => $sesion['expira'],
            'usuario' => $sesion['usuario'],
            'roles'   => $sesion['roles'],
        ]);
    }

    /**
     * GET /api/sesion (Protegido con JWT)
     */
    public function quienSoy(array $quien): void
    {
        $this->responderJson(200, [
            'estado'   => 200,
            'username' => $quien['username'],
            'roles'    => $quien['roles'],
            'expira'   => $quien['exp'],
        ]);
    }

    /**
     * DELETE /api/sesion (Protegido con JWT)
     */
    public function salir(array $quien): void
    {
        $this->responderJson(200, [
            'estado'   => 200,
            'mensaje'  => 'Sesion cerrada. Borre el token en el cliente.',
            'username' => $quien['username'],
        ]);
    }

    private function responderJson(int $estado, array $cuerpo): void
    {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    }
}
