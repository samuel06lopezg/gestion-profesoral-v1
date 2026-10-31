<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\Beca;
use PDO;
use PDOException;

class RepositorioBecaMariaDB implements IRepositorioBeca
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT b.estudios, b.tipo, b.institucion, b.fecha_inicio, b.fecha_fin,
                       er.titulo AS estudios_titulo
                FROM beca b
                LEFT JOIN estudios_realizados er ON b.estudios = er.id
                WHERE b.activo = TRUE
                LIMIT :limite";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $lista = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = $this->mapear($fila);
        }

        return $lista;
    }

    public function obtenerPorId(int $estudios): ?Beca
    {
        $sql = "SELECT b.estudios, b.tipo, b.institucion, b.fecha_inicio, b.fecha_fin,
                       er.titulo AS estudios_titulo
                FROM beca b
                LEFT JOIN estudios_realizados er ON b.estudios = er.id
                WHERE b.estudios = :estudios AND b.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':estudios' => $estudios]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(Beca $beca): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM beca WHERE estudios = :estudios"
            );
            $stmtCheck->execute([':estudios' => $beca->estudios]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'El estudio seleccionado ya tiene una beca registrada.',
                        409
                    );
                }
                $sql = "UPDATE beca SET 
                        tipo = :tipo, institucion = :institucion, 
                        fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, activo = TRUE 
                        WHERE estudios = :estudios";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':estudios'     => $beca->estudios,
                    ':tipo'         => $beca->tipo,
                    ':institucion'  => $beca->institucion,
                    ':fecha_inicio' => $beca->fechaInicio,
                    ':fecha_fin'    => $beca->fechaFin
                ]);
            }

            $sql = "INSERT INTO beca (estudios, tipo, institucion, fecha_inicio, fecha_fin, activo) 
                    VALUES (:estudios, :tipo, :institucion, :fecha_inicio, :fecha_fin, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':estudios'     => $beca->estudios,
                ':tipo'         => $beca->tipo,
                ':institucion'  => $beca->institucion,
                ':fecha_inicio' => $beca->fechaInicio,
                ':fecha_fin'    => $beca->fechaFin
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizar(Beca $beca): bool
    {
        try {
            $sql = "UPDATE beca SET 
                    tipo = :tipo, institucion = :institucion, 
                    fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin 
                    WHERE estudios = :estudios AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':estudios'     => $beca->estudios,
                ':tipo'         => $beca->tipo,
                ':institucion'  => $beca->institucion,
                ':fecha_inicio' => $beca->fechaInicio,
                ':fecha_fin'    => $beca->fechaFin
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizarParcial(int $estudios, array $datos): bool
    {
        if (empty($datos)) {
            return false;
        }

        try {
            $campos = [];
            $parametros = [':estudios' => $estudios];

            foreach ($datos as $columna => $valor) {
                $campos[] = "{$columna} = :{$columna}";
                $parametros[":{$columna}"] = $valor;
            }

            $sql = "UPDATE beca SET " . implode(', ', $campos) .
                   " WHERE estudios = :estudios AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function eliminar(int $estudios): bool
    {
        try {
            $sql = "UPDATE beca SET activo = FALSE 
                    WHERE estudios = :estudios AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':estudios' => $estudios]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): Beca
    {
        return new Beca(
            estudios: (int)$fila['estudios'],
            tipo: $fila['tipo'],
            institucion: $fila['institucion'],
            fechaInicio: $fila['fecha_inicio'],
            fechaFin: $fila['fecha_fin'] ?? null,
            estudiosTitulo: $fila['estudios_titulo'] ?? null
        );
    }
}
