<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\EstudioAreaConocimiento;

interface IRepositorioEstudioAreaConocimiento
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorClaves(int $estudio, string $areaConocimiento): ?EstudioAreaConocimiento;
    public function crear(EstudioAreaConocimiento $eac): bool;
    public function eliminar(int $estudio, string $areaConocimiento): bool;
}
