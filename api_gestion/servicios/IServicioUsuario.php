<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Usuario;

interface IServicioUsuario
{
    /**
     * @return Usuario[]
     */
    public function obtenerTodos(): array;

    public function obtenerPorId(int $id): Usuario;

    public function obtenerPorUsername(string $username): Usuario;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Usuario;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): Usuario;

    public function eliminar(int $id): bool;
}
