<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\EstudiosRealizados;
use PDO;
use PDOException;

require_once __DIR__ . '/errores_de_integridad.php';

class RepositorioEstudiosRealizadosMariaDB implements IRepositorioEstudiosRealizados
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_listar_estudios_realizados()');
            $sentencia->execute();
            $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (empty($filas)) {
                return [];
            }

            if ($limite > 0 && count($filas) > $limite) {
                $filas = array_slice($filas, 0, $limite);
            }

            return array_map(fn(array $f) => $this->hidratarEstudio($f), $filas);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function obtenerPorId(int $id): ?EstudiosRealizados
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_consultar_estudio_realizado(?)');
            $sentencia->execute([$id]);
            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (!$fila) {
                return null;
            }

            return $this->hidratarEstudio($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function obtenerPorDocente(int $cedulaDocente): array
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_listar_estudios_por_docente(?)');
            $sentencia->execute([$cedulaDocente]);
            $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            return array_map(fn(array $f) => $this->hidratarEstudio($f), $filas);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function crear(array $datos): EstudiosRealizados
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_crear_estudio_realizado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $sentencia->execute([
                (string)$datos['titulo'],
                (string)$datos['universidad'],
                (string)$datos['fecha'],
                (string)$datos['tipo'],
                (string)$datos['ciudad'],
                (int)$datos['docente'],
                (int)($datos['ins_acreditada'] ?? 0),
                (string)$datos['metodologia'],
                (string)($datos['perfil_egresado'] ?? ''),
                (string)$datos['pais']
            ]);

            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            return $this->hidratarEstudio($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): ?EstudiosRealizados
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_actualizar_estudio_realizado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $sentencia->execute([
                $id,
                (string)$datos['titulo'],
                (string)$datos['universidad'],
                (string)$datos['fecha'],
                (string)$datos['tipo'],
                (string)$datos['ciudad'],
                (int)$datos['docente'],
                (int)($datos['ins_acreditada'] ?? 0),
                (string)$datos['metodologia'],
                (string)($datos['perfil_egresado'] ?? ''),
                (string)$datos['pais']
            ]);

            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (!$fila) {
                return null;
            }

            return $this->hidratarEstudio($fila);
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
            $sentencia = $this->conexion->prepare('CALL sp_eliminar_estudio_realizado(?)');
            $sentencia->execute([$id]);
            $afectadas = $sentencia->rowCount();
            $sentencia->closeCursor();

            return $afectadas > 0;
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function contarPorDocente(int $cedulaDocente): int
    {
        $sentencia = $this->conexion->prepare('SELECT COUNT(*) FROM estudios_realizados WHERE docente = ? AND activo = 1');
        $sentencia->execute([$cedulaDocente]);
        $total = (int)$sentencia->fetchColumn();
        $sentencia->closeCursor();
        return $total;
    }

    private function hidratarEstudio(array $fila): EstudiosRealizados
    {
        return new EstudiosRealizados(
            id: (int)$fila['id'],
            titulo: (string)$fila['titulo'],
            universidad: (string)$fila['universidad'],
            fecha: (string)$fila['fecha'],
            tipo: (string)$fila['tipo'],
            ciudad: (string)$fila['ciudad'],
            docente: (int)$fila['docente'],
            insAcreditada: (int)$fila['ins_acreditada'],
            metodologia: (string)$fila['metodologia'],
            perfilEgresado: (string)$fila['perfil_egresado'],
            pais: (string)$fila['pais']
        );
    }
}
