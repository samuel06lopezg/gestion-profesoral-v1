<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\EstudioAreaConocimiento;

interface IServicioEstudioAreaConocimiento
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorClaves(int $estudio, string $areaConocimiento): EstudioAreaConocimiento;
    public function crear(array $datos): bool;
    public function retirar(int $estudio, string $areaConocimiento): bool;
}
