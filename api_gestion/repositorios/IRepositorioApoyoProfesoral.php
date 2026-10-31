<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\ApoyoProfesoral;

interface IRepositorioApoyoProfesoral
{
    public function obtenerTodos(int $limite): array;
    public function obtenerPorId(int $estudios): ?ApoyoProfesoral;
    public function crear(ApoyoProfesoral $apoyo): bool;
    public function actualizar(ApoyoProfesoral $apoyo): bool;
    public function actualizarParcial(int $estudios, array $datos): bool;
    public function eliminar(int $estudios): bool;
}
