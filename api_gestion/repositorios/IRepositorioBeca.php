<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Beca;

interface IRepositorioBeca
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorId(int $estudios): ?Beca;
    public function crear(Beca $beca): bool;
    public function actualizar(Beca $beca): bool;
    public function actualizarParcial(int $estudios, array $datos): bool;
    public function eliminar(int $estudios): bool;
}
