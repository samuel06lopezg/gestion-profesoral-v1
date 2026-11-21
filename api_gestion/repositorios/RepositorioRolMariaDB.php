<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Rol;
use PDO;

class RepositorioRolMariaDB implements IRepositorioRol
{
    public function __construct(private PDO $conexion)
    {
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT id, nombre, descripcion, activo, fecha_creacion
                FROM rol
                WHERE activo = 1
                ORDER BY id ASC";
        $stmt = $this->conexion->query($sql);

        $roles = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = Rol::desdeArreglo($fila);
        }
        return $roles;
    }

    public function obtenerPorId(int $id): ?Rol
    {
        $sql = "SELECT id, nombre, descripcion, activo, fecha_creacion
                FROM rol
                WHERE id = :id AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? Rol::desdeArreglo($fila) : null;
    }

    public function obtenerPorNombre(string $nombre): ?Rol
    {
        $sql = "SELECT id, nombre, descripcion, activo, fecha_creacion
                FROM rol
                WHERE nombre = :nombre AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['nombre' => $nombre]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? Rol::desdeArreglo($fila) : null;
    }

    public function crear(Rol $rol): ?Rol
    {
        $sql = "INSERT INTO rol (nombre, descripcion, activo)
                VALUES (:nombre, :descripcion, :activo)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'nombre'      => $rol->nombre,
            'descripcion' => $rol->descripcion,
            'activo'      => $rol->activo,
        ]);

        $id = (int) $this->conexion->lastInsertId();
        return $this->obtenerPorId($id);
    }

    public function actualizar(Rol $rol): bool
    {
        $sql = "UPDATE rol
                SET nombre = :nombre, descripcion = :descripcion
                WHERE id = :id AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            'id'          => $rol->id,
            'nombre'      => $rol->nombre,
            'descripcion' => $rol->descripcion,
        ]);
    }

    public function eliminar(int $id): bool
    {
        // Borrado lógico
        $sql = "UPDATE rol SET activo = 0 WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
