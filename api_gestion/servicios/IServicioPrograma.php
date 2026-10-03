<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Programa;

interface IServicioPrograma
{
    /**
     * @param int $limite
     * @return Programa[]
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): Programa;

    public function crear(array $datos): bool;

    public function reemplazar(int $id, array $datos): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    public function retirar(int $id): bool;
}