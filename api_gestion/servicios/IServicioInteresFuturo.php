<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\InteresFuturo;

interface IServicioInteresFuturo
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorClaves(int $docente, string $terminoClave): InteresFuturo;
    public function crear(array $datos): bool;
    public function retirar(int $docente, string $terminoClave): bool;
}
