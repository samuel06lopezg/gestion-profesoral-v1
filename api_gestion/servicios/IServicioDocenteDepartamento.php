<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\DocenteDepartamento;

interface IServicioDocenteDepartamento
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorClaves(int $docente, int $departamento): DocenteDepartamento;
    public function crear(array $datos): bool;
    public function reemplazar(int $docente, int $departamento, array $datos): bool;
    public function actualizarParcial(int $docente, int $departamento, array $datos): bool;
    public function retirar(int $docente, int $departamento): bool;
}
