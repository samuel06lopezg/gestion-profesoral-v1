<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\RedDocente;

interface IServicioRedDocente
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorClaves(int $red, int $docente): RedDocente;
    public function crear(array $datos): bool;
    public function reemplazar(int $red, int $docente, array $datos): bool;
    public function actualizarParcial(int $red, int $docente, array $datos): bool;
    public function retirar(int $red, int $docente): bool;
}
