<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\EvaluacionDocente;
use PDO;
use PDOException;

require_once __DIR__ . '/errores_de_integridad.php';

class RepositorioEvaluacionDocenteMariaDB implements IRepositorioEvaluacionDocente
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        try {
            $sql = 'SELECT id, calificacion, semestre, docente FROM evaluacion_docente WHERE activo = 1 ORDER BY id DESC LIMIT :limite';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->bindValue(':limite', $limite, PDO::PARAM_INT);
            $sentencia->execute();
            $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn(array $f) => $this->hidratar($f), $filas);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function obtenerPorId(int $id): ?EvaluacionDocente
    {
        try {
            $sql = 'SELECT id, calificacion, semestre, docente FROM evaluacion_docente WHERE id = :id AND activo = 1';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();
            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                return null;
            }

            return $this->hidratar($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function crear(array $datos): EvaluacionDocente
    {
        try {
            $sql = 'INSERT INTO evaluacion_docente (calificacion, semestre, docente, activo) VALUES (:calificacion, :semestre, :docente, 1)';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':calificacion' => (float)$datos['calificacion'],
                ':semestre'     => (string)$datos['semestre'],
                ':docente'      => (int)$datos['docente']
            ]);

            $idGenerado = (int)$this->conexion->lastInsertId();
            return new EvaluacionDocente($idGenerado, (float)$datos['calificacion'], (string)$datos['semestre'], (int)$datos['docente']);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): ?EvaluacionDocente
    {
        try {
            $sql = 'UPDATE evaluacion_docente SET calificacion = :calificacion, semestre = :semestre, docente = :docente WHERE id = :id AND activo = 1';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':id'           => $id,
                ':calificacion' => (float)$datos['calificacion'],
                ':semestre'     => (string)$datos['semestre'],
                ':docente'      => (int)$datos['docente']
            ]);

            return $this->obtenerPorId($id);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizarParcial(int $id, array $datos): ?EvaluacionDocente
    {
        try {
            $actual = $this->obtenerPorId($id);
            if (!$actual) {
                return null;
            }

            $calificacion = isset($datos['calificacion']) ? (float)$datos['calificacion'] : $actual->calificacion;
            $semestre     = isset($datos['semestre']) ? (string)$datos['semestre'] : $actual->semestre;
            $docente      = isset($datos['docente']) ? (int)$datos['docente'] : $actual->docente;

            return $this->actualizar($id, [
                'calificacion' => $calificacion,
                'semestre'     => $semestre,
                'docente'      => $docente
            ]);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function retirar(int $id): bool
    {
        try {
            $sql = 'UPDATE evaluacion_docente SET activo = 0 WHERE id = :id AND activo = 1';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();

            return $sentencia->rowCount() > 0;
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    private function hidratar(array $fila): EvaluacionDocente
    {
        return new EvaluacionDocente(
            (int)$fila['id'],
            (float)$fila['calificacion'],
            (string)$fila['semestre'],
            (int)$fila['docente']
        );
    }
}
