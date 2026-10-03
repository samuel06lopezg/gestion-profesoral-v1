<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Red;

// La lista de promesas del servicio de red.
// El controlador dependerá de ESTA interfaz, no de la clase concreta.
interface IServicioRed
{
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorId(int $id): Red;

    public function crear(array $datos): bool;

    public function reemplazar(int $id, array $datos): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    public function retirar(int $id): bool;
}