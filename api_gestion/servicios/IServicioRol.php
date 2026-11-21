<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Rol;

interface IServicioRol
{
    /**
     * @return Rol[]
     */
    public function obtenerTodos(): array;

    public function obtenerPorId(int $id): Rol;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Rol;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): Rol;

    public function eliminar(int $id): bool;
}
