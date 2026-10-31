<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\RedDocente;
use PDO;
use PDOException;

class RepositorioRedDocenteMariaDB implements IRepositorioRedDocente
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT rd.red, rd.docente, rd.fecha_inicio, rd.fecha_fin, rd.act_destacadas,
                       r.nombre AS red_nombre,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre
                FROM red_docente rd
                LEFT JOIN red r ON rd.red = r.idr
                LEFT JOIN docente d ON rd.docente = d.cedula
                WHERE rd.activo = TRUE
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

    public function obtenerPorClaves(int $red, int $docente): ?RedDocente
    {
        $sql = "SELECT rd.red, rd.docente, rd.fecha_inicio, rd.fecha_fin, rd.act_destacadas,
                       r.nombre AS red_nombre,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre
                FROM red_docente rd
                LEFT JOIN red r ON rd.red = r.idr
                LEFT JOIN docente d ON rd.docente = d.cedula
                WHERE rd.red = :red AND rd.docente = :docente AND rd.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':red' => $red, ':docente' => $docente]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(RedDocente $rd): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM red_docente WHERE red = :red AND docente = :docente"
            );
            $stmtCheck->execute([':red' => $rd->red, ':docente' => $rd->docente]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'El docente ya está vinculado a esta red académica.',
                        409
                    );
                }
                $sql = "UPDATE red_docente SET 
                        fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, 
                        act_destacadas = :act_destacadas, activo = TRUE 
                        WHERE red = :red AND docente = :docente";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':red'            => $rd->red,
                    ':docente'        => $rd->docente,
                    ':fecha_inicio'   => $rd->fechaInicio,
                    ':fecha_fin'      => $rd->fechaFin,
                    ':act_destacadas' => $rd->actDestacadas
                ]);
            }

            $sql = "INSERT INTO red_docente (red, docente, fecha_inicio, fecha_fin, act_destacadas, activo) 
                    VALUES (:red, :docente, :fecha_inicio, :fecha_fin, :act_destacadas, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':red'            => $rd->red,
                ':docente'        => $rd->docente,
                ':fecha_inicio'   => $rd->fechaInicio,
                ':fecha_fin'      => $rd->fechaFin,
                ':act_destacadas' => $rd->actDestacadas
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizar(RedDocente $rd): bool
    {
        try {
            $sql = "UPDATE red_docente SET 
                    fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, 
                    act_destacadas = :act_destacadas 
                    WHERE red = :red AND docente = :docente AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':red'            => $rd->red,
                ':docente'        => $rd->docente,
                ':fecha_inicio'   => $rd->fechaInicio,
                ':fecha_fin'      => $rd->fechaFin,
                ':act_destacadas' => $rd->actDestacadas
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizarParcial(int $red, int $docente, array $datos): bool
    {
        if (empty($datos)) {
            return false;
        }

        try {
            $campos = [];
            $parametros = [':red' => $red, ':docente' => $docente];

            foreach ($datos as $columna => $valor) {
                $campos[] = "{$columna} = :{$columna}";
                $parametros[":{$columna}"] = $valor;
            }

            $sql = "UPDATE red_docente SET " . implode(', ', $campos) .
                   " WHERE red = :red AND docente = :docente AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function eliminar(int $red, int $docente): bool
    {
        try {
            $sql = "UPDATE red_docente SET activo = FALSE 
                    WHERE red = :red AND docente = :docente AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':red' => $red, ':docente' => $docente]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): RedDocente
    {
        return new RedDocente(
            red: (int)$fila['red'],
            docente: (int)$fila['docente'],
            fechaInicio: $fila['fecha_inicio'],
            fechaFin: $fila['fecha_fin'] ?? null,
            actDestacadas: $fila['act_destacadas'] ?? '',
            redNombre: $fila['red_nombre'] ?? null,
            docenteNombre: $fila['docente_nombre'] ?? null
        );
    }
}
