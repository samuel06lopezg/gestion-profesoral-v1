<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Rol;

interface IRepositorioRol
{
    /**
     * @return Rol[]
     */
    public function obtenerTodos(): array;

    public function obtenerPorId(int $id): ?Rol;

    public function obtenerPorNombre(string $nombre): ?Rol;

    public function crear(Rol $rol): ?Rol;

    public function actualizar(Rol $rol): bool;

    public function eliminar(int $id): bool;
}
