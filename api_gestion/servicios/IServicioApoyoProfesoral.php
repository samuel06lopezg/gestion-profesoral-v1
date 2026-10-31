<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\ApoyoProfesoral;

interface IServicioApoyoProfesoral
{
    public function obtenerTodos(int $limite = 1000): array;
    public function obtenerPorId(int $estudios): ApoyoProfesoral;
    public function crear(array $datos): bool;
    public function reemplazar(int $estudios, array $datos): bool;
    public function actualizarParcial(int $estudios, array $datos): bool;
    public function retirar(int $estudios): bool;
}
