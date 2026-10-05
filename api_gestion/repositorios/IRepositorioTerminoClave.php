<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\TerminoClave;

interface IRepositorioTerminoClave
{
    public function obtenerTodos(int $limite): array;

    public function obtenerPorTermino(string $termino): ?TerminoClave;

    public function crear(TerminoClave $termino): bool;

    public function actualizar(TerminoClave $termino): bool;

    public function actualizarParcial(string $termino, array $datos): bool;

    // Borrado lógico: pone activo = FALSE
    public function eliminar(string $termino): bool;
}