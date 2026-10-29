<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\EvaluacionDocente;

interface IRepositorioEvaluacionDocente
{
    /**
     * @return array<int, EvaluacionDocente>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): ?EvaluacionDocente;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): EvaluacionDocente;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): ?EvaluacionDocente;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizarParcial(int $id, array $datos): ?EvaluacionDocente;

    public function retirar(int $id): bool;
}
