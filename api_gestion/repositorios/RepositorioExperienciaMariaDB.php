<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Experiencia;
use PDO;
use PDOException;

require_once __DIR__ . '/errores_de_integridad.php';

class RepositorioExperienciaMariaDB implements IRepositorioExperiencia
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        try {
            $sql = 'SELECT id, nombre_cargo, institucion, tipo, fecha_inicio, fecha_fin, docente FROM experiecia WHERE activo = 1 ORDER BY id DESC LIMIT :limite';
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

    public function obtenerPorId(int $id): ?Experiencia
    {
        try {
            $sql = 'SELECT id, nombre_cargo, institucion, tipo, fecha_inicio, fecha_fin, docente FROM experiecia WHERE id = :id AND activo = 1';
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

    public function crear(array $datos): Experiencia
    {
        try {
            $sql = 'INSERT INTO experiecia (nombre_cargo, institucion, tipo, fecha_inicio, fecha_fin, docente, activo)
                    VALUES (:nombre_cargo, :institucion, :tipo, :fecha_inicio, :fecha_fin, :docente, 1)';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':nombre_cargo' => (string)$datos['nombre_cargo'],
                ':institucion'  => (string)$datos['institucion'],
                ':tipo'         => (string)$datos['tipo'],
                ':fecha_inicio' => (string)$datos['fecha_inicio'],
                ':fecha_fin'    => !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null,
                ':docente'      => (int)$datos['docente']
            ]);

            $idGenerado = (int)$this->conexion->lastInsertId();
            return new Experiencia(
                id: $idGenerado,
                nombreCargo: (string)$datos['nombre_cargo'],
                institucion: (string)$datos['institucion'],
                tipo: (string)$datos['tipo'],
                fechaInicio: (string)$datos['fecha_inicio'],
                docente: (int)$datos['docente'],
                fechaFin: !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null
            );
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): ?Experiencia
    {
        try {
            $sql = 'UPDATE experiecia SET
                        nombre_cargo = :nombre_cargo,
                        institucion  = :institucion,
                        tipo         = :tipo,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin    = :fecha_fin,
                        docente      = :docente
                    WHERE id = :id AND activo = 1';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':id'           => $id,
                ':nombre_cargo' => (string)$datos['nombre_cargo'],
                ':institucion'  => (string)$datos['institucion'],
                ':tipo'         => (string)$datos['tipo'],
                ':fecha_inicio' => (string)$datos['fecha_inicio'],
                ':fecha_fin'    => !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null,
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

    public function actualizarParcial(int $id, array $datos): ?Experiencia
    {
        try {
            $actual = $this->obtenerPorId($id);
            if (!$actual) {
                return null;
            }

            return $this->actualizar($id, [
                'nombre_cargo' => $datos['nombre_cargo'] ?? $actual->nombreCargo,
                'institucion'  => $datos['institucion'] ?? $actual->institucion,
                'tipo'         => $datos['tipo'] ?? $actual->tipo,
                'fecha_inicio' => $datos['fecha_inicio'] ?? $actual->fechaInicio,
                'fecha_fin'    => array_key_exists('fecha_fin', $datos) ? $datos['fecha_fin'] : $actual->fechaFin,
                'docente'      => $datos['docente'] ?? $actual->docente
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
            $sql = 'UPDATE experiecia SET activo = 0 WHERE id = :id AND activo = 1';
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

    private function hidratar(array $fila): Experiencia
    {
        return new Experiencia(
            id: (int)$fila['id'],
            nombreCargo: (string)$fila['nombre_cargo'],
            institucion: (string)$fila['institucion'],
            tipo: (string)$fila['tipo'],
            fechaInicio: (string)$fila['fecha_inicio'],
            docente: (int)$fila['docente'],
            fechaFin: isset($fila['fecha_fin']) ? (string)$fila['fecha_fin'] : null
        );
    }
}
