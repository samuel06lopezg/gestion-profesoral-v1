<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\AreaConocimiento;

interface IServicioAreaConocimiento
{
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(string $id): AreaConocimiento;

    public function crear(array $datos): bool;

    public function reemplazar(string $id, array $datos): bool;

    public function actualizarParcial(string $id, array $datos): bool;

    public function retirar(string $id): bool;
}