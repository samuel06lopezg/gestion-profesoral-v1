<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\LineaInvestigacion;
use InvalidArgumentException;
use PDO;

// El ÚNICO archivo que escribe SQL para linea_investigacion.
class RepositorioLineaInvestigacionMariaDB implements IRepositorioLineaInvestigacion
{
    // Lista blanca de columnas que PATCH puede cambiar (ni "id" ni "activo")
    private const CAMPOS_EDITABLES = ['nombre', 'descripcion'];

    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT id, nombre, descripcion FROM linea_investigacion
                WHERE activo = TRUE LIMIT :limite";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $lineas = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lineas[] = $this->mapear($fila);
        }
        return $lineas;
    }

    public function obtenerPorId(int $id): ?LineaInvestigacion
    {
        $sql = "SELECT id, nombre, descripcion FROM linea_investigacion
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    // OJO: aquí NO se inserta el id. La base lo genera (AUTO_INCREMENT)
    // y lastInsertId() nos dice cuál le tocó.
    public function crear(LineaInvestigacion $linea): int
    {
        $sql = "INSERT INTO linea_investigacion (nombre, descripcion, activo)
                VALUES (:nombre, :descripcion, TRUE)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':nombre'      => $linea->nombre,
            ':descripcion' => $linea->descripcion,
        ]);
        return (int)$this->conexion->lastInsertId();
    }

    public function actualizar(LineaInvestigacion $linea): bool
    {
        $sql = "UPDATE linea_investigacion SET nombre = :nombre, descripcion = :descripcion
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id'          => $linea->id,
            ':nombre'      => $linea->nombre,
            ':descripcion' => $linea->descripcion,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        $campos = [];
        $valores = [':id' => $id];
        foreach (self::CAMPOS_EDITABLES as $columna) {
            if (array_key_exists($columna, $datos)) {
                $campos[] = "{$columna} = :{$columna}";
                $valores[":{$columna}"] = $datos[$columna];
            }
        }

        if (empty($campos)) {
            throw new InvalidArgumentException('No se envió ningún campo editable.');
        }

        $sql = "UPDATE linea_investigacion SET " . implode(', ', $campos) . "
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount() > 0;
    }

    // Borrado LÓGICO
    public function eliminar(int $id): bool
    {
        $sql = "UPDATE linea_investigacion SET activo = FALSE WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapear(array $fila): LineaInvestigacion
    {
        return new LineaInvestigacion((int)$fila['id'], $fila['nombre'], $fila['descripcion']);
    }
}