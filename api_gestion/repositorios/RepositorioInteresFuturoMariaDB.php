<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\InteresFuturo;
use PDO;
use PDOException;

class RepositorioInteresFuturoMariaDB implements IRepositorioInteresFuturo
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT ifu.docente, ifu.termino_clave,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre
                FROM intereses_futuros ifu
                LEFT JOIN docente d ON ifu.docente = d.cedula
                WHERE ifu.activo = TRUE
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

    public function obtenerPorClaves(int $docente, string $terminoClave): ?InteresFuturo
    {
        $sql = "SELECT ifu.docente, ifu.termino_clave,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre
                FROM intereses_futuros ifu
                LEFT JOIN docente d ON ifu.docente = d.cedula
                WHERE ifu.docente = :docente AND ifu.termino_clave = :termino_clave AND ifu.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':docente' => $docente, ':termino_clave' => $terminoClave]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(InteresFuturo $interes): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM intereses_futuros WHERE docente = :docente AND termino_clave = :termino_clave"
            );
            $stmtCheck->execute([
                ':docente'       => $interes->docente,
                ':termino_clave' => $interes->terminoClave
            ]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'El interés de investigación ya se encuentra asignado a este docente.',
                        409
                    );
                }
                $sql = "UPDATE intereses_futuros SET activo = TRUE 
                        WHERE docente = :docente AND termino_clave = :termino_clave";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':docente'       => $interes->docente,
                    ':termino_clave' => $interes->terminoClave
                ]);
            }

            $sql = "INSERT INTO intereses_futuros (docente, termino_clave, activo) 
                    VALUES (:docente, :termino_clave, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':docente'       => $interes->docente,
                ':termino_clave' => $interes->terminoClave
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function eliminar(int $docente, string $terminoClave): bool
    {
        try {
            $sql = "UPDATE intereses_futuros SET activo = FALSE 
                    WHERE docente = :docente AND termino_clave = :termino_clave AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':docente'       => $docente,
                ':termino_clave' => $terminoClave
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): InteresFuturo
    {
        return new InteresFuturo(
            docente: (int)$fila['docente'],
            terminoClave: $fila['termino_clave'],
            docenteNombre: $fila['docente_nombre'] ?? null
        );
    }
}
