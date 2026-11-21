<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Usuario;

interface IRepositorioUsuario
{
    /**
     * @return Usuario[]
     */
    public function obtenerTodos(): array;

    public function obtenerPorId(int $id): ?Usuario;

    public function obtenerPorUsername(string $username): ?Usuario;

    public function obtenerPorEmail(string $email): ?Usuario;

    public function crear(Usuario $usuario): ?Usuario;

    public function actualizar(Usuario $usuario, bool $actualizarClave = false): bool;

    public function eliminar(int $id): bool;
}
