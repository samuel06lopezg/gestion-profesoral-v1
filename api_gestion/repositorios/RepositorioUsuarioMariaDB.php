<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Usuario;
use PDO;

class RepositorioUsuarioMariaDB implements IRepositorioUsuario
{
    public function __construct(private PDO $conexion)
    {
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT id, username, password, email, nombre_completo, activo,
                       fecha_creacion, fecha_actualizacion
                FROM usuario
                WHERE activo = 1
                ORDER BY id ASC";
        $stmt = $this->conexion->query($sql);

        $usuarios = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles = $this->obtenerNombresRolesDeUsuario((int) $fila['id']);
            $usuarios[] = Usuario::desdeArreglo($fila, $roles);
        }
        return $usuarios;
    }

    public function obtenerPorId(int $id): ?Usuario
    {
        $sql = "SELECT id, username, password, email, nombre_completo, activo,
                       fecha_creacion, fecha_actualizacion
                FROM usuario
                WHERE id = :id AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $roles = $this->obtenerNombresRolesDeUsuario($id);
        return Usuario::desdeArreglo($fila, $roles);
    }

    public function obtenerPorUsername(string $username): ?Usuario
    {
        $sql = "SELECT id, username, password, email, nombre_completo, activo,
                       fecha_creacion, fecha_actualizacion
                FROM usuario
                WHERE username = :username AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['username' => $username]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $roles = $this->obtenerNombresRolesDeUsuario((int) $fila['id']);
        return Usuario::desdeArreglo($fila, $roles);
    }

    public function obtenerPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT id, username, password, email, nombre_completo, activo,
                       fecha_creacion, fecha_actualizacion
                FROM usuario
                WHERE email = :email AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $roles = $this->obtenerNombresRolesDeUsuario((int) $fila['id']);
        return Usuario::desdeArreglo($fila, $roles);
    }

    public function crear(Usuario $usuario): ?Usuario
    {
        $sql = "INSERT INTO usuario (username, password, email, nombre_completo, activo)
                VALUES (:username, :password, :email, :nombre_completo, :activo)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'username'        => $usuario->username,
            'password'        => $usuario->password,
            'email'           => $usuario->email,
            'nombre_completo' => $usuario->nombreCompleto,
            'activo'          => $usuario->activo,
        ]);

        $id = (int) $this->conexion->lastInsertId();
        return $this->obtenerPorId($id);
    }

    public function actualizar(Usuario $usuario, bool $actualizarClave = false): bool
    {
        if ($actualizarClave) {
            $sql = "UPDATE usuario
                    SET username = :username, password = :password, email = :email,
                        nombre_completo = :nombre_completo
                    WHERE id = :id AND activo = 1";
            $parametros = [
                'id'              => $usuario->id,
                'username'        => $usuario->username,
                'password'        => $usuario->password,
                'email'           => $usuario->email,
                'nombre_completo' => $usuario->nombreCompleto,
            ];
        } else {
            $sql = "UPDATE usuario
                    SET username = :username, email = :email, nombre_completo = :nombre_completo
                    WHERE id = :id AND activo = 1";
            $parametros = [
                'id'              => $usuario->id,
                'username'        => $usuario->username,
                'email'           => $usuario->email,
                'nombre_completo' => $usuario->nombreCompleto,
            ];
        }

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($parametros);
    }

    public function eliminar(int $id): bool
    {
        // Borrado lógico
        $sql = "UPDATE usuario SET activo = 0 WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * @return string[]
     */
    private function obtenerNombresRolesDeUsuario(int $usuarioId): array
    {
        $sql = "SELECT r.nombre
                FROM rol r
                INNER JOIN rol_usuario ru ON r.id = ru.rol_id
                WHERE ru.usuario_id = :usuario_id AND r.activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
