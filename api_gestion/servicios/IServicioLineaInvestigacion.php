<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\LineaInvestigacion;

interface IServicioLineaInvestigacion
{
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): LineaInvestigacion;

    // Devuelve el id generado por la base
    public function crear(array $datos): int;

    public function reemplazar(int $id, array $datos): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    public function retirar(int $id): bool;
}