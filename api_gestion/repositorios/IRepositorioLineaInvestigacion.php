<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\LineaInvestigacion;

interface IRepositorioLineaInvestigacion
{
    public function obtenerTodos(int $limite): array;

    public function obtenerPorId(int $id): ?LineaInvestigacion;

    // Devuelve el id que la base le asignó a la fila nueva
    public function crear(LineaInvestigacion $linea): int;

    public function actualizar(LineaInvestigacion $linea): bool;

    public function actualizarParcial(int $id, array $datos): bool;

    // Borrado lógico: pone activo = FALSE
    public function eliminar(int $id): bool;
}
