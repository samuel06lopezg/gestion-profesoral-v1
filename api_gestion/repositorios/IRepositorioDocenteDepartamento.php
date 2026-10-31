<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\DocenteDepartamento;

interface IRepositorioDocenteDepartamento
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorClaves(int $docente, int $departamento): ?DocenteDepartamento;
    public function crear(DocenteDepartamento $dd): bool;
    public function actualizar(DocenteDepartamento $dd): bool;
    public function actualizarParcial(int $docente, int $departamento, array $datos): bool;
    public function eliminar(int $docente, int $departamento): bool;
}
