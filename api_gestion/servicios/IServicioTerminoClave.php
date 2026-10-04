<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\TerminoClave;

interface IServicioTerminoClave
{
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorTermino(string $termino): TerminoClave;

    public function crear(array $datos): bool;

    public function reemplazar(string $termino, array $datos): bool;

    public function actualizarParcial(string $termino, array $datos): bool;

    public function retirar(string $termino): bool;
}