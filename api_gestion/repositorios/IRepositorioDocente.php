<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Docente;
use PDO;

interface IRepositorioDocente
{
    /**
     * @return array<int, Docente>
     */
    public function obtenerTodos(int $limite = 1000): array;

    public function obtenerPorCedula(int $cedula): ?Docente;

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Docente;

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $cedula, array $datos): ?Docente;

    public function retirar(int $cedula): bool;

    public function obtenerConexion(): PDO;
}
