<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\RolUsuario;
use ApiGestion\Repositorios\IRepositorioRol;
use ApiGestion\Repositorios\IRepositorioRolUsuario;
use ApiGestion\Repositorios\IRepositorioUsuario;

class ServicioRolUsuario implements IServicioRolUsuario
{
    public function __construct(
        private readonly IRepositorioRolUsuario $repositorio,
        private readonly IRepositorioUsuario $repositorioUsuario,
        private readonly IRepositorioRol $repositorioRol
    ) {
    }

    public function obtenerTodos(): array
    {
        return $this->repositorio->obtenerTodos();
    }

    public function obtenerRolesPorUsuario(int $usuarioId): array
    {
        return $this->repositorio->obtenerRolesPorUsuario($usuarioId);
    }

    public function asignarRol(int $usuarioId, int $rolId): bool
    {
        $usuario = $this->repositorioUsuario->obtenerPorId($usuarioId);
        if ($usuario === null) {
            throw new ConflictoIntegridadExcepcion("No existe el usuario con ID {$usuarioId}.");
        }

        $rol = $this->repositorioRol->obtenerPorId($rolId);
        if ($rol === null) {
            throw new ConflictoIntegridadExcepcion("No existe el rol con ID {$rolId}.");
        }

        // Igual que las demás tablas puente: una asignación repetida es un conflicto (409)
        if ($this->repositorio->tieneRol($usuarioId, $rolId)) {
            throw new ConflictoIntegridadExcepcion('El usuario ya tiene asignado ese rol.');
        }

        return $this->repositorio->asignarRol($usuarioId, $rolId);
    }

    public function desasignarRol(int $usuarioId, int $rolId): bool
    {
        return $this->repositorio->desasignarRol($usuarioId, $rolId);
    }
}
