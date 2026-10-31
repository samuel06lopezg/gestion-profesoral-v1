<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Modelos\Docente;

interface IServicioDocente
{
    /**
     * @return array<int, Docente>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorCedula(int $cedula, bool $incluirEstudios = true): Docente;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Docente;

    /**
     * @param array<string, mixed> $datos
     */
    public function reemplazar(int $cedula, array $datos): Docente;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizarParcial(int $cedula, array $datos): Docente;

    public function retirar(int $cedula): bool;
}
