<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\AreaConocimiento;
use InvalidArgumentException;
use PDO;

// El ÚNICO archivo que escribe SQL para area_conocimiento.
class RepositorioAreaConocimientoMariaDB implements IRepositorioAreaConocimiento
{
    // La llave (id) NO se puede cambiar por PATCH, solo estos tres campos.
    private const CAMPOS_EDITABLES = ['gran_area', 'area', 'disciplina'];

    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT id, gran_area, area, disciplina FROM area_conocimiento
                WHERE activo = TRUE LIMIT :limite";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $areas = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $areas[] = $this->mapear($fila);
        }
        return $areas;
    }

    public function obtenerPorId(string $id): ?AreaConocimiento
    {
        $sql = "SELECT id, gran_area, area, disciplina FROM area_conocimiento
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(AreaConocimiento $area): bool
    {
        $sql = "INSERT INTO area_conocimiento (id, gran_area, area, disciplina, activo)
                VALUES (:id, :gran_area, :area, :disciplina, TRUE)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':id'         => $area->id,
            ':gran_area'  => $area->granArea,
            ':area'       => $area->area,
            ':disciplina' => $area->disciplina,
        ]);
    }

    public function actualizar(AreaConocimiento $area): bool
    {
        $sql = "UPDATE area_conocimiento
                SET gran_area = :gran_area, area = :area, disciplina = :disciplina
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id'         => $area->id,
            ':gran_area'  => $area->granArea,
            ':area'       => $area->area,
            ':disciplina' => $area->disciplina,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarParcial(string $id, array $datos): bool
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

        $sql = "UPDATE area_conocimiento SET " . implode(', ', $campos) . "
                WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount() > 0;
    }

    // Borrado LÓGICO
    public function eliminar(string $id): bool
    {
        $sql = "UPDATE area_conocimiento SET activo = FALSE WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapear(array $fila): AreaConocimiento
    {
        // OJO: el id se queda como TEXTO (string). Un (int) convertiría '1A01' en 1.
        return new AreaConocimiento((string)$fila['id'], $fila['gran_area'], $fila['area'], $fila['disciplina']);
    }
}