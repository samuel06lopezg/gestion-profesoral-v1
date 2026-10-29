<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Experiencia;

interface IServicioExperiencia
{
    /**
     * @return array<int, Experiencia>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): Experiencia;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Experiencia;

    /**
     * @param array<string, mixed> $datos
     */
    public function reemplazar(int $id, array $datos): Experiencia;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizarParcial(int $id, array $datos): Experiencia;

    public function retirar(int $id): bool;
}
