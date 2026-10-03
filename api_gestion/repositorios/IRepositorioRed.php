<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Red;

interface IRepositorioRed
{
    public function obtenerTodos(int $limite): array;

    public function obtenerPorId(int $id): ?Red;

    public function crear(Red $red): bool;

    public function actualizar(Red $red): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    // Borrado lógico: pone activo = FALSE, no borra la fila
    public function eliminar(int $id): bool;
}