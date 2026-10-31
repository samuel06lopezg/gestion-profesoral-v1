<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Docente;
use PDO;
use PDOException;

require_once __DIR__ . '/errores_de_integridad.php';

class RepositorioDocenteMariaDB implements IRepositorioDocente
{
    public function __construct(private PDO $conexion) {}

    public function obtenerConexion(): PDO
    {
        return $this->conexion;
    }

    public function obtenerTodos(int $limite = 1000): array
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_listar_docente()');
            $sentencia->execute();
            $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (empty($filas)) {
                return [];
            }

            if ($limite > 0 && count($filas) > $limite) {
                $filas = array_slice($filas, 0, $limite);
            }

            return array_map(fn(array $f) => $this->hidratarDocente($f), $filas);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function obtenerPorCedula(int $cedula): ?Docente
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_consultar_docente(?)');
            $sentencia->execute([$cedula]);
            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (!$fila) {
                return null;
            }

            return $this->hidratarDocente($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function crear(array $datos): Docente
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_crear_docente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $sentencia->execute([
                (int)$datos['cedula'],
                (string)$datos['nombres'],
                (string)$datos['apellidos'],
                (string)$datos['genero'],
                (string)$datos['cargo'],
                (string)$datos['fecha_nacimiento'],
                (string)$datos['correo'],
                (string)$datos['telefono'],
                (string)$datos['url_cvlac'],
                (string)$datos['fecha_actualizacion'],
                (string)$datos['escalafon'],
                (string)$datos['perfil'],
                isset($datos['cat_minciencia']) && $datos['cat_minciencia'] !== '' ? (string)$datos['cat_minciencia'] : null,
                (string)$datos['conv_minciencia'],
                (string)$datos['nacionalidaad'],
                isset($datos['linea_investigacion_principal']) && $datos['linea_investigacion_principal'] !== '' && $datos['linea_investigacion_principal'] !== null
                    ? (int)$datos['linea_investigacion_principal']
                    : null
            ]);

            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            return $this->hidratarDocente($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function actualizar(int $cedula, array $datos): ?Docente
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_actualizar_docente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $sentencia->execute([
                $cedula,
                (string)$datos['nombres'],
                (string)$datos['apellidos'],
                (string)$datos['genero'],
                (string)$datos['cargo'],
                (string)$datos['fecha_nacimiento'],
                (string)$datos['correo'],
                (string)$datos['telefono'],
                (string)$datos['url_cvlac'],
                (string)$datos['fecha_actualizacion'],
                (string)$datos['escalafon'],
                (string)$datos['perfil'],
                isset($datos['cat_minciencia']) && $datos['cat_minciencia'] !== '' ? (string)$datos['cat_minciencia'] : null,
                (string)$datos['conv_minciencia'],
                (string)$datos['nacionalidaad'],
                isset($datos['linea_investigacion_principal']) && $datos['linea_investigacion_principal'] !== '' && $datos['linea_investigacion_principal'] !== null
                    ? (int)$datos['linea_investigacion_principal']
                    : null
            ]);

            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            $sentencia->closeCursor();

            if (!$fila) {
                return null;
            }

            return $this->hidratarDocente($fila);
        } catch (PDOException $e) {
            $conflicto = mapearErrorIntegridad($e);
            if ($conflicto !== null) {
                throw $conflicto;
            }
            throw $e;
        }
    }

    public function retirar(int $cedula): bool
    {
        try {
            $sentencia = $this->conexion->prepare('CALL sp_eliminar_docente(?)');
            $sentencia->execute([$cedula]);
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

    private function hidratarDocente(array $fila): Docente
    {
        return new Docente(
            cedula: (int)$fila['cedula'],
            nombres: (string)$fila['nombres'],
            apellidos: (string)$fila['apellidos'],
            genero: (string)$fila['genero'],
            cargo: (string)$fila['cargo'],
            fechaNacimiento: (string)$fila['fecha_nacimiento'],
            correo: (string)$fila['correo'],
            telefono: (string)$fila['telefono'],
            urlCvlac: (string)$fila['url_cvlac'],
            fechaActualizacion: (string)$fila['fecha_actualizacion'],
            escalafon: (string)$fila['escalafon'],
            perfil: (string)$fila['perfil'],
            convMinciencia: (string)$fila['conv_minciencia'],
            nacionalidaad: (string)$fila['nacionalidaad'],
            catMinciencia: isset($fila['cat_minciencia']) ? (string)$fila['cat_minciencia'] : null,
            lineaInvestigacionPrincipal: isset($fila['linea_investigacion_principal']) && $fila['linea_investigacion_principal'] !== null
                ? (int)$fila['linea_investigacion_principal']
                : null
        );
    }
}
