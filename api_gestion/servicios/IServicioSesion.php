<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

interface IServicioSesion
{
    /**
     * Valida credenciales de acceso y emite un token JWT si son correctas.
     * Retorna array con datos de sesión o null si las credenciales fallan.
     *
     * @return array<string, mixed>|null
     */
    public function entrar(string $username, string $password): ?array;

    /**
     * Devuelve la información del usuario autenticado a partir de su username.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerInfoSesion(string $username): ?array;
}
