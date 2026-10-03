<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Programa;

interface IRepositorioPrograma
{
    /**
     * @param int $limite
     * @return Programa[]
     */
    public function obtenerTodos(int $limite): array;

    public function obtenerPorId(int $id): ?Programa;

    public function crear(Programa $programa): bool;

    public function actualizar(Programa $programa): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    /**
     * Realiza un borrado lógico estableciendo activo = FALSE.
     */
    public function eliminar(int $id): bool;
}