<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Rol;
use ApiGestion\Modelos\RolUsuario;
use PDO;

class RepositorioRolUsuarioMariaDB implements IRepositorioRolUsuario
{
    public function __construct(private PDO $conexion)
    {
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT ru.usuario_id, ru.rol_id, r.nombre AS nombre_rol
                FROM rol_usuario ru
                INNER JOIN rol r ON ru.rol_id = r.id
                INNER JOIN usuario u ON ru.usuario_id = u.id
                WHERE r.activo = 1 AND u.activo = 1
                ORDER BY ru.usuario_id ASC";
        $stmt = $this->conexion->query($sql);

        $asignaciones = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asignaciones[] = RolUsuario::desdeArreglo($fila);
        }
        return $asignaciones;
    }

    public function obtenerRolesPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT r.id, r.nombre, r.descripcion, r.activo, r.fecha_creacion
                FROM rol r
                INNER JOIN rol_usuario ru ON r.id = ru.rol_id
                WHERE ru.usuario_id = :usuario_id AND r.activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);

        $roles = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = Rol::desdeArreglo($fila);
        }
        return $roles;
    }

    public function asignarRol(int $usuarioId, int $rolId): bool
    {
        $sql = "INSERT INTO rol_usuario (usuario_id, rol_id)
                VALUES (:usuario_id, :rol_id)
                ON DUPLICATE KEY UPDATE usuario_id = usuario_id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'rol_id'     => $rolId,
        ]);
    }

    public function desasignarRol(int $usuarioId, int $rolId): bool
    {
        $sql = "DELETE FROM rol_usuario
                WHERE usuario_id = :usuario_id AND rol_id = :rol_id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'rol_id'     => $rolId,
        ]);
    }

    public function tieneRol(int $usuarioId, int $rolId): bool
    {
        $sql = "SELECT COUNT(*) FROM rol_usuario
                WHERE usuario_id = :usuario_id AND rol_id = :rol_id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'rol_id'     => $rolId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
