<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Reconocimiento;

interface IServicioReconocimiento
{
    /**
     * @return array<int, Reconocimiento>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): Reconocimiento;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Reconocimiento;

    /**
     * @param array<string, mixed> $datos
     */
    public function reemplazar(int $id, array $datos): Reconocimiento;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizarParcial(int $id, array $datos): Reconocimiento;

    public function retirar(int $id): bool;
}
