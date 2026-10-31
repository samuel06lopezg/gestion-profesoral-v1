<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Beca;

interface IServicioBeca
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorId(int $estudios): Beca;
    public function crear(array $datos): bool;
    public function reemplazar(int $estudios, array $datos): bool;
    public function actualizarParcial(int $estudios, array $datos): bool;
    public function retirar(int $estudios): bool;
}
