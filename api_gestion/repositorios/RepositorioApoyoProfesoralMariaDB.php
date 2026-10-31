<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\ApoyoProfesoral;
use PDO;
use PDOException;

class RepositorioApoyoProfesoralMariaDB implements IRepositorioApoyoProfesoral
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT ap.estudios, ap.con_apoyo, ap.institucion, ap.tipo,
                       er.titulo AS estudios_titulo
                FROM apoyo_profesoral ap
                LEFT JOIN estudios_realizados er ON ap.estudios = er.id
                WHERE ap.activo = TRUE
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

    public function obtenerPorId(int $estudios): ?ApoyoProfesoral
    {
        $sql = "SELECT ap.estudios, ap.con_apoyo, ap.institucion, ap.tipo,
                       er.titulo AS estudios_titulo
                FROM apoyo_profesoral ap
                LEFT JOIN estudios_realizados er ON ap.estudios = er.id
                WHERE ap.estudios = :estudios AND ap.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':estudios' => $estudios]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(ApoyoProfesoral $apoyo): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM apoyo_profesoral WHERE estudios = :estudios"
            );
            $stmtCheck->execute([':estudios' => $apoyo->estudios]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'El estudio seleccionado ya tiene un apoyo profesoral registrado.',
                        409
                    );
                }
                $sql = "UPDATE apoyo_profesoral SET 
                        con_apoyo = :con_apoyo, institucion = :institucion, tipo = :tipo, activo = TRUE 
                        WHERE estudios = :estudios";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':estudios'    => $apoyo->estudios,
                    ':con_apoyo'   => $apoyo->conApoyo,
                    ':institucion' => $apoyo->institucion,
                    ':tipo'        => $apoyo->tipo
                ]);
            }

            $sql = "INSERT INTO apoyo_profesoral (estudios, con_apoyo, institucion, tipo, activo) 
                    VALUES (:estudios, :con_apoyo, :institucion, :tipo, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':estudios'    => $apoyo->estudios,
                ':con_apoyo'   => $apoyo->conApoyo,
                ':institucion' => $apoyo->institucion,
                ':tipo'        => $apoyo->tipo
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizar(ApoyoProfesoral $apoyo): bool
    {
        try {
            $sql = "UPDATE apoyo_profesoral SET 
                    con_apoyo = :con_apoyo, institucion = :institucion, tipo = :tipo 
                    WHERE estudios = :estudios AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':estudios'    => $apoyo->estudios,
                ':con_apoyo'   => $apoyo->conApoyo,
                ':institucion' => $apoyo->institucion,
                ':tipo'        => $apoyo->tipo
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

            $sql = "UPDATE apoyo_profesoral SET " . implode(', ', $campos) .
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
            $sql = "UPDATE apoyo_profesoral SET activo = FALSE 
                    WHERE estudios = :estudios AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':estudios' => $estudios]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): ApoyoProfesoral
    {
        return new ApoyoProfesoral(
            estudios: (int)$fila['estudios'],
            conApoyo: (int)$fila['con_apoyo'],
            institucion: $fila['institucion'],
            tipo: $fila['tipo'],
            estudiosTitulo: $fila['estudios_titulo'] ?? null
        );
    }
}
