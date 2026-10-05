<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\TerminoClave;
use InvalidArgumentException;
use PDO;

// El ÚNICO archivo que escribe SQL para termino_clave.
class RepositorioTerminoClaveMariaDB implements IRepositorioTerminoClave
{
    // La llave (termino) NO se puede cambiar: solo la traducción.
    private const CAMPOS_EDITABLES = ['termino_ingles'];

    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT termino, termino_ingles FROM termino_clave
                WHERE activo = TRUE LIMIT :limite";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $terminos = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $terminos[] = $this->mapear($fila);
        }
        return $terminos;
    }

    public function obtenerPorTermino(string $termino): ?TerminoClave
    {
        $sql = "SELECT termino, termino_ingles FROM termino_clave
                WHERE termino = :termino AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':termino' => $termino]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(TerminoClave $termino): bool
    {
        $sql = "INSERT INTO termino_clave (termino, termino_ingles, activo)
                VALUES (:termino, :ingles, TRUE)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':termino' => $termino->termino,
            ':ingles'  => $termino->terminoIngles,
        ]);
    }

    public function actualizar(TerminoClave $termino): bool
    {
        $sql = "UPDATE termino_clave SET termino_ingles = :ingles
                WHERE termino = :termino AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':termino' => $termino->termino,
            ':ingles'  => $termino->terminoIngles,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarParcial(string $termino, array $datos): bool
    {
        $campos = [];
        $valores = [':termino' => $termino];
        foreach (self::CAMPOS_EDITABLES as $columna) {
            if (array_key_exists($columna, $datos)) {
                $campos[] = "{$columna} = :{$columna}";
                $valores[":{$columna}"] = $datos[$columna];
            }
        }

        if (empty($campos)) {
            throw new InvalidArgumentException('No se envió ningún campo editable.');
        }

        $sql = "UPDATE termino_clave SET " . implode(', ', $campos) . "
                WHERE termino = :termino AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount() > 0;
    }

    // Borrado LÓGICO
    public function eliminar(string $termino): bool
    {
        $sql = "UPDATE termino_clave SET activo = FALSE WHERE termino = :termino AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':termino' => $termino]);
        return $stmt->rowCount() > 0;
    }

    private function mapear(array $fila): TerminoClave
    {
        return new TerminoClave($fila['termino'], $fila['termino_ingles']);
    }
}