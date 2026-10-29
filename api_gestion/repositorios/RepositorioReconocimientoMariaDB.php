<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Reconocimiento;
use PDO;
use PDOException;

require_once __DIR__ . '/errores_de_integridad.php';

class RepositorioReconocimientoMariaDB implements IRepositorioReconocimiento
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        try {
            $sql = 'SELECT id, tipo, fecha, institucion, nombre, ambito, docente FROM reconocimiento WHERE activo = 1 ORDER BY id DESC LIMIT :limite';
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

    public function obtenerPorId(int $id): ?Reconocimiento
    {
        try {
            $sql = 'SELECT id, tipo, fecha, institucion, nombre, ambito, docente FROM reconocimiento WHERE id = :id AND activo = 1';
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

    public function crear(array $datos): Reconocimiento
    {
        try {
            $sql = 'INSERT INTO reconocimiento (tipo, fecha, institucion, nombre, ambito, docente, activo)
                    VALUES (:tipo, :fecha, :institucion, :nombre, :ambito, :docente, 1)';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':tipo'        => (string)$datos['tipo'],
                ':fecha'       => (string)$datos['fecha'],
                ':institucion' => (string)$datos['institucion'],
                ':nombre'      => (string)$datos['nombre'],
                ':ambito'      => (string)$datos['ambito'],
                ':docente'     => (int)$datos['docente']
            ]);

            $idGenerado = (int)$this->conexion->lastInsertId();
            return new Reconocimiento(
                id: $idGenerado,
                tipo: (string)$datos['tipo'],
                fecha: (string)$datos['fecha'],
                institucion: (string)$datos['institucion'],
                nombre: (string)$datos['nombre'],
                ambito: (string)$datos['ambito'],
                docente: (int)$datos['docente']
            );
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): ?Reconocimiento
    {
        try {
            $sql = 'UPDATE reconocimiento SET
                        tipo        = :tipo,
                        fecha       = :fecha,
                        institucion = :institucion,
                        nombre      = :nombre,
                        ambito      = :ambito,
                        docente     = :docente
                    WHERE id = :id AND activo = 1';
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([
                ':id'          => $id,
                ':tipo'        => (string)$datos['tipo'],
                ':fecha'       => (string)$datos['fecha'],
                ':institucion' => (string)$datos['institucion'],
                ':nombre'      => (string)$datos['nombre'],
                ':ambito'      => (string)$datos['ambito'],
                ':docente'     => (int)$datos['docente']
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

    public function actualizarParcial(int $id, array $datos): ?Reconocimiento
    {
        try {
            $actual = $this->obtenerPorId($id);
            if (!$actual) {
                return null;
            }

            return $this->actualizar($id, [
                'tipo'        => $datos['tipo'] ?? $actual->tipo,
                'fecha'       => $datos['fecha'] ?? $actual->fecha,
                'institucion' => $datos['institucion'] ?? $actual->institucion,
                'nombre'      => $datos['nombre'] ?? $actual->nombre,
                'ambito'      => $datos['ambito'] ?? $actual->ambito,
                'docente'     => $datos['docente'] ?? $actual->docente
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
            $sql = 'UPDATE reconocimiento SET activo = 0 WHERE id = :id AND activo = 1';
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

    private function hidratar(array $fila): Reconocimiento
    {
        return new Reconocimiento(
            id: (int)$fila['id'],
            tipo: (string)$fila['tipo'],
            fecha: (string)$fila['fecha'],
            institucion: (string)$fila['institucion'],
            nombre: (string)$fila['nombre'],
            ambito: (string)$fila['ambito'],
            docente: (int)$fila['docente']
        );
    }
}
