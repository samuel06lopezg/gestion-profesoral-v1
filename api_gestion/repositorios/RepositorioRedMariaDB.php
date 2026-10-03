<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Red;
use InvalidArgumentException;
use PDO;

// Repositorio de red: es el ÚNICO archivo que escribe SQL para esta tabla.
// "implements IRepositorioRed" = promete hacer las 6 cosas de la interfaz.
class RepositorioRedMariaDB implements IRepositorioRed
{
    // Qué columnas se pueden cambiar con PATCH (lista blanca).
    // La llave (idr) y "activo" NO están: el cliente no puede tocarlas.
    private const CAMPOS_EDITABLES = ['nombre', 'url', 'pais'];

    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT idr, nombre, url, pais FROM red
                WHERE activo = TRUE LIMIT :limite";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $redes = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $redes[] = $this->mapearRed($fila);
        }
        return $redes;
    }

    public function obtenerPorId(int $id): ?Red
    {
        $sql = "SELECT idr, nombre, url, pais FROM red
                WHERE idr = :idr AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':idr' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearRed($fila) : null;
    }

    public function crear(Red $red): bool
    {
        $sql = "INSERT INTO red (idr, nombre, url, pais, activo)
                VALUES (:idr, :nombre, :url, :pais, TRUE)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':idr'    => $red->idr,
            ':nombre' => $red->nombre,
            ':url'    => $red->url,
            ':pais'   => $red->pais,
        ]);
    }

    public function actualizar(Red $red): bool
    {
        $sql = "UPDATE red SET nombre = :nombre, url = :url, pais = :pais
                WHERE idr = :idr AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':idr'    => $red->idr,
            ':nombre' => $red->nombre,
            ':url'    => $red->url,
            ':pais'   => $red->pais,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        // Solo se aceptan las columnas de la lista blanca
        $campos = [];
        $valores = [':idr' => $id];
        foreach (self::CAMPOS_EDITABLES as $columna) {
            if (array_key_exists($columna, $datos)) {
                $campos[] = "{$columna} = :{$columna}";
                $valores[":{$columna}"] = $datos[$columna];
            }
        }

        if (empty($campos)) {
            throw new InvalidArgumentException('No se envió ningún campo editable.');
        }

        $sql = "UPDATE red SET " . implode(', ', $campos) . "
                WHERE idr = :idr AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount() > 0;
    }

    // Borrado LÓGICO: la fila se queda, solo se marca activo = FALSE
    public function eliminar(int $id): bool
    {
        $sql = "UPDATE red SET activo = FALSE WHERE idr = :idr AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':idr' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapearRed(array $fila): Red
    {
        return new Red((int)$fila['idr'], $fila['nombre'], $fila['url'], $fila['pais']);
    }
}