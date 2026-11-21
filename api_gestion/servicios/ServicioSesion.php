<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Repositorios\IRepositorioUsuario;

require_once __DIR__ . '/../autorizacion/jwt.php';

class ServicioSesion implements IServicioSesion
{
    public function __construct(
        private readonly IRepositorioUsuario $repositorioUsuario
    ) {
    }

    public function entrar(string $username, string $password): ?array
    {
        $usuario = $this->repositorioUsuario->obtenerPorUsername($username);

        // Si no existe o no está activo, o la contraseña no coincide: null genérico
        // Se previene la enumeración de usuarios garantizando respuesta uniforme
        if ($usuario === null || $usuario->activo !== 1) {
            return null;
        }

        if (!password_verify($password, $usuario->password)) {
            return null;
        }

        // Generar token JWT con los roles asociados
        [$token, $expira] = jwt_firmar($usuario->username, $usuario->roles);

        return [
            'token'   => $token,
            'expira'  => $expira,
            'usuario' => [
                'id'              => $usuario->id,
                'username'        => $usuario->username,
                'email'           => $usuario->email,
                'nombre_completo' => $usuario->nombreCompleto,
            ],
            'roles'   => $usuario->roles,
        ];
    }

    public function obtenerInfoSesion(string $username): ?array
    {
        $usuario = $this->repositorioUsuario->obtenerPorUsername($username);
        if ($usuario === null) {
            return null;
        }

        return [
            'id'              => $usuario->id,
            'username'        => $usuario->username,
            'email'           => $usuario->email,
            'nombre_completo' => $usuario->nombreCompleto,
            'roles'           => $usuario->roles,
        ];
    }
}
