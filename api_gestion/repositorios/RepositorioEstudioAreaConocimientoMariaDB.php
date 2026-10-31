<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\EstudioAreaConocimiento;
use PDO;
use PDOException;

class RepositorioEstudioAreaConocimientoMariaDB implements IRepositorioEstudioAreaConocimiento
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT eac.estudio, eac.area_conocimiento,
                       er.titulo AS estudio_titulo,
                       ac.disciplina AS area_disciplina
                FROM estudio_ac eac
                LEFT JOIN estudios_realizados er ON eac.estudio = er.id
                LEFT JOIN area_conocimiento ac ON eac.area_conocimiento = ac.id
                WHERE eac.activo = TRUE
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

    public function obtenerPorClaves(int $estudio, string $areaConocimiento): ?EstudioAreaConocimiento
    {
        $sql = "SELECT eac.estudio, eac.area_conocimiento,
                       er.titulo AS estudio_titulo,
                       ac.disciplina AS area_disciplina
                FROM estudio_ac eac
                LEFT JOIN estudios_realizados er ON eac.estudio = er.id
                LEFT JOIN area_conocimiento ac ON eac.area_conocimiento = ac.id
                WHERE eac.estudio = :estudio AND eac.area_conocimiento = :area_conocimiento AND eac.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':estudio'           => $estudio,
            ':area_conocimiento' => $areaConocimiento
        ]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(EstudioAreaConocimiento $eac): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM estudio_ac WHERE estudio = :estudio AND area_conocimiento = :area_conocimiento"
            );
            $stmtCheck->execute([
                ':estudio'           => $eac->estudio,
                ':area_conocimiento' => $eac->areaConocimiento
            ]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'Esta área de conocimiento ya está asociada al estudio indicado.',
                        409
                    );
                }
                $sql = "UPDATE estudio_ac SET activo = TRUE 
                        WHERE estudio = :estudio AND area_conocimiento = :area_conocimiento";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':estudio'           => $eac->estudio,
                    ':area_conocimiento' => $eac->areaConocimiento
                ]);
            }

            $sql = "INSERT INTO estudio_ac (estudio, area_conocimiento, activo) 
                    VALUES (:estudio, :area_conocimiento, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':estudio'           => $eac->estudio,
                ':area_conocimiento' => $eac->areaConocimiento
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function eliminar(int $estudio, string $areaConocimiento): bool
    {
        try {
            $sql = "UPDATE estudio_ac SET activo = FALSE 
                    WHERE estudio = :estudio AND area_conocimiento = :area_conocimiento AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':estudio'           => $estudio,
                ':area_conocimiento' => $areaConocimiento
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): EstudioAreaConocimiento
    {
        return new EstudioAreaConocimiento(
            estudio: (int)$fila['estudio'],
            areaConocimiento: $fila['area_conocimiento'],
            estudioTitulo: $fila['estudio_titulo'] ?? null,
            areaDisciplina: $fila['area_disciplina'] ?? null
        );
    }
}
