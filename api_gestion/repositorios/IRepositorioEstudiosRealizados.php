<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\EstudiosRealizados;

interface IRepositorioEstudiosRealizados
{
    /**
     * @return array<int, EstudiosRealizados>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): ?EstudiosRealizados;

    /**
     * @return array<int, EstudiosRealizados>
     */
    public function obtenerPorDocente(int $cedulaDocente): array;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): EstudiosRealizados;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): ?EstudiosRealizados;

    public function retirar(int $id): bool;

    public function contarPorDocente(int $cedulaDocente): int;
}
