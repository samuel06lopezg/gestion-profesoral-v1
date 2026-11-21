<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Usuario;
use ApiGestion\Repositorios\IRepositorioUsuario;
use PDOException;

class ServicioUsuario implements IServicioUsuario
{
    public function __construct(
        private readonly IRepositorioUsuario $repositorio
    ) {
    }

    public function obtenerTodos(): array
    {
        return $this->repositorio->obtenerTodos();
    }

    public function obtenerPorId(int $id): Usuario
    {
        $usuario = $this->repositorio->obtenerPorId($id);
        if ($usuario === null) {
            throw new NoEncontradoExcepcion("No se encontro el usuario con ID {$id}.");
        }
        return $usuario;
    }

    public function obtenerPorUsername(string $username): Usuario
    {
        $usuario = $this->repositorio->obtenerPorUsername($username);
        if ($usuario === null) {
            throw new NoEncontradoExcepcion("No se encontro el usuario '{$username}'.");
        }
        return $usuario;
    }

    public function crear(array $datos): Usuario
    {
        $username = trim((string) ($datos['username'] ?? ''));
        $password = (string) ($datos['password'] ?? '');
        $email    = trim((string) ($datos['email'] ?? ''));

        if ($username === '') {
            throw new ConflictoIntegridadExcepcion("El campo username es obligatorio.");
        }
        if ($password === '') {
            throw new ConflictoIntegridadExcepcion("El campo password es obligatorio.");
        }
        if ($email === '') {
            throw new ConflictoIntegridadExcepcion("El campo email es obligatorio.");
        }

        if ($this->repositorio->obtenerPorUsername($username) !== null) {
            throw new ConflictoIntegridadExcepcion("El nombre de usuario '{$username}' ya se encuentra registrado.");
        }
        if ($this->repositorio->obtenerPorEmail($email) !== null) {
            throw new ConflictoIntegridadExcepcion("El correo electronico '{$email}' ya se encuentra registrado.");
        }

        // Hasheo de la clave con BCRYPT en estricto cumplimiento del Artículo 9
        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $usuario = new Usuario(
            id: null,
            username: $username,
            password: $hashPassword,
            email: $email,
            nombreCompleto: isset($datos['nombre_completo']) ? trim((string) $datos['nombre_completo']) : null,
            activo: 1
        );

        try {
            $nuevo = $this->repositorio->crear($usuario);
            if ($nuevo === null) {
                throw new ConflictoIntegridadExcepcion("No se pudo registrar el usuario.");
            }
            return $nuevo;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                throw new ConflictoIntegridadExcepcion("Conflicto: username o correo electronico duplicado.");
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): Usuario
    {
        $usuario = $this->obtenerPorId($id);
        $actualizarClave = false;

        if (isset($datos['username'])) {
            $nuevoUsername = trim((string) $datos['username']);
            if ($nuevoUsername === '') {
                throw new ConflictoIntegridadExcepcion("El username no puede quedar vacio.");
            }
            $usuario->username = $nuevoUsername;
        }

        if (isset($datos['email'])) {
            $nuevoEmail = trim((string) $datos['email']);
            if ($nuevoEmail === '') {
                throw new ConflictoIntegridadExcepcion("El correo electronico no puede quedar vacio.");
            }
            $usuario->email = $nuevoEmail;
        }

        if (array_key_exists('nombre_completo', $datos)) {
            $usuario->nombreCompleto = $datos['nombre_completo'] !== null ? trim((string) $datos['nombre_completo']) : null;
        }

        if (!empty($datos['password'])) {
            $usuario->password = password_hash((string) $datos['password'], PASSWORD_BCRYPT);
            $actualizarClave = true;
        }

        $this->repositorio->actualizar($usuario, $actualizarClave);
        return $this->obtenerPorId($id);
    }

    public function eliminar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->eliminar($id);
    }
}
