<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Modelos\DocenteDepartamento;
use PDO;
use PDOException;

class RepositorioDocenteDepartamentoMariaDB implements IRepositorioDocenteDepartamento
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT dd.docente, dd.departamento, dd.dedicacion, dd.modalidad, 
                       dd.fecha_ingreso, dd.fecha_salida,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre,
                       p.nombre AS departamento_nombre
                FROM docente_departamento dd
                LEFT JOIN docente d ON dd.docente = d.cedula
                LEFT JOIN programa p ON dd.departamento = p.id
                WHERE dd.activo = TRUE
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

    public function obtenerPorClaves(int $docente, int $departamento): ?DocenteDepartamento
    {
        $sql = "SELECT dd.docente, dd.departamento, dd.dedicacion, dd.modalidad, 
                       dd.fecha_ingreso, dd.fecha_salida,
                       CONCAT(d.nombres, ' ', d.apellidos) AS docente_nombre,
                       p.nombre AS departamento_nombre
                FROM docente_departamento dd
                LEFT JOIN docente d ON dd.docente = d.cedula
                LEFT JOIN programa p ON dd.departamento = p.id
                WHERE dd.docente = :docente AND dd.departamento = :departamento AND dd.activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':docente' => $docente, ':departamento' => $departamento]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapear($fila) : null;
    }

    public function crear(DocenteDepartamento $dd): bool
    {
        try {
            $stmtCheck = $this->conexion->prepare(
                "SELECT activo FROM docente_departamento WHERE docente = :docente AND departamento = :departamento"
            );
            $stmtCheck->execute([':docente' => $dd->docente, ':departamento' => $dd->departamento]);
            $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($fila !== false) {
                if ((int)$fila['activo'] === 1) {
                    throw new ConflictoIntegridadExcepcion(
                        'La vinculación del docente con este departamento ya existe en el sistema.',
                        409
                    );
                }
                // Reactivación si estaba retirado previamente
                $sql = "UPDATE docente_departamento SET 
                        dedicacion = :dedicacion, modalidad = :modalidad, 
                        fecha_ingreso = :fecha_ingreso, fecha_salida = :fecha_salida, 
                        activo = TRUE 
                        WHERE docente = :docente AND departamento = :departamento";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([
                    ':docente'       => $dd->docente,
                    ':departamento'  => $dd->departamento,
                    ':dedicacion'    => $dd->dedicacion,
                    ':modalidad'     => $dd->modalidad,
                    ':fecha_ingreso' => $dd->fechaIngreso,
                    ':fecha_salida'  => $dd->fechaSalida
                ]);
            }

            $sql = "INSERT INTO docente_departamento 
                    (docente, departamento, dedicacion, modalidad, fecha_ingreso, fecha_salida, activo) 
                    VALUES (:docente, :departamento, :dedicacion, :modalidad, :fecha_ingreso, :fecha_salida, TRUE)";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':docente'       => $dd->docente,
                ':departamento'  => $dd->departamento,
                ':dedicacion'    => $dd->dedicacion,
                ':modalidad'     => $dd->modalidad,
                ':fecha_ingreso' => $dd->fechaIngreso,
                ':fecha_salida'  => $dd->fechaSalida
            ]);
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizar(DocenteDepartamento $dd): bool
    {
        try {
            $sql = "UPDATE docente_departamento SET 
                    dedicacion = :dedicacion, modalidad = :modalidad, 
                    fecha_ingreso = :fecha_ingreso, fecha_salida = :fecha_salida 
                    WHERE docente = :docente AND departamento = :departamento AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':docente'       => $dd->docente,
                ':departamento'  => $dd->departamento,
                ':dedicacion'    => $dd->dedicacion,
                ':modalidad'     => $dd->modalidad,
                ':fecha_ingreso' => $dd->fechaIngreso,
                ':fecha_salida'  => $dd->fechaSalida
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function actualizarParcial(int $docente, int $departamento, array $datos): bool
    {
        if (empty($datos)) {
            return false;
        }

        try {
            $campos = [];
            $parametros = [':docente' => $docente, ':departamento' => $departamento];

            foreach ($datos as $columna => $valor) {
                $campos[] = "{$columna} = :{$columna}";
                $parametros[":{$columna}"] = $valor;
            }

            $sql = "UPDATE docente_departamento SET " . implode(', ', $campos) .
                   " WHERE docente = :docente AND departamento = :departamento AND activo = TRUE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    public function eliminar(int $docente, int $departamento): bool
    {
        try {
            $sql = "UPDATE docente_departamento SET activo = FALSE 
                    WHERE docente = :docente AND departamento = :departamento AND activo = TRUE";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':docente' => $docente, ':departamento' => $departamento]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            ManejadorIntegridad::procesar($e);
        }
    }

    private function mapear(array $fila): DocenteDepartamento
    {
        return new DocenteDepartamento(
            docente: (int)$fila['docente'],
            departamento: (int)$fila['departamento'],
            dedicacion: $fila['dedicacion'],
            modalidad: $fila['modalidad'],
            fechaIngreso: $fila['fecha_ingreso'],
            fechaSalida: $fila['fecha_salida'] ?? null,
            docenteNombre: $fila['docente_nombre'] ?? null,
            departamentoNombre: $fila['departamento_nombre'] ?? null
        );
    }
}
