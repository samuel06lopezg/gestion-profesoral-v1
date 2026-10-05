<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\AreaConocimiento;

interface IRepositorioAreaConocimiento
{
    public function obtenerTodos(int $limite): array;

    public function obtenerPorId(string $id): ?AreaConocimiento;

    public function crear(AreaConocimiento $area): bool;

    public function actualizar(AreaConocimiento $area): bool;

    public function actualizarParcial(string $id, array $datos): bool;

    // Borrado lógico: pone activo = FALSE
    public function eliminar(string $id): bool;
}