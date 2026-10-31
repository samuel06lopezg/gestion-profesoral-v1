<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\InteresFuturo;

interface IRepositorioInteresFuturo
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorClaves(int $docente, string $terminoClave): ?InteresFuturo;
    public function crear(InteresFuturo $interes): bool;
    public function eliminar(int $docente, string $terminoClave): bool;
}
