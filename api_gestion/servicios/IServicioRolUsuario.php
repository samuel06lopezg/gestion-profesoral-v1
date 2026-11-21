<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Rol;
use ApiGestion\Modelos\RolUsuario;

interface IServicioRolUsuario
{
    /**
     * @return RolUsuario[]
     */
    public function obtenerTodos(): array;

    /**
     * @return Rol[]
     */
    public function obtenerRolesPorUsuario(int $usuarioId): array;

    public function asignarRol(int $usuarioId, int $rolId): bool;

    public function desasignarRol(int $usuarioId, int $rolId): bool;
}
