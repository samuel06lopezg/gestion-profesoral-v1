<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\RedDocente;

interface IRepositorioRedDocente
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorClaves(int $red, int $docente): ?RedDocente;
    public function crear(RedDocente $rd): bool;
    public function actualizar(RedDocente $rd): bool;
    public function actualizarParcial(int $red, int $docente, array $datos): bool;
    public function eliminar(int $red, int $docente): bool;
}
