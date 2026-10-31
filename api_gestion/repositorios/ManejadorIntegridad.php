<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use PDOException;

class ManejadorIntegridad
{
    /**
     * Evalúa una PDOException producida por MariaDB. Si es una violación
     * de integridad referencial o regla de negocio, lanza ConflictoIntegridadExcepcion (409).
     * De lo contrario, relanza la excepción original (para terminar en 500).
     *
     * @throws ConflictoIntegridadExcepcion
     * @throws PDOException
     */
    public static function procesar(PDOException $e): never
    {
        $sqlState = (string)($e->errorInfo[0] ?? '');
        $codigoMotor = (int)($e->errorInfo[1] ?? 0);
        $mensajeMotor = (string)($e->errorInfo[2] ?? $e->getMessage());

        // 1. Regla de negocio disparada por SIGNAL SQLSTATE '45000' (errno 1644)
        if ($sqlState === '45000') {
            throw new ConflictoIntegridadExcepcion($mensajeMotor, 409, $e);
        }

        // 2. Registro duplicado (clave primaria simple o compuesta en tabla puente)
        if ($codigoMotor === 1062) {
            throw new ConflictoIntegridadExcepcion(
                'El registro o asignación ya existe en el sistema (registro duplicado).',
                409,
                $e
            );
        }

        // 3. Padre inexistente (clave foránea apunta a registro que no existe)
        if ($codigoMotor === 1452) {
            throw new ConflictoIntegridadExcepcion(
                'No se puede guardar el registro porque uno de los datos relacionados no existe en la base de datos.',
                409,
                $e
            );
        }

        // 4. Intento de modificar o retirar fila padre con hijos activos
        if ($codigoMotor === 1451) {
            throw new ConflictoIntegridadExcepcion(
                'No se puede retirar o modificar este registro porque tiene dependencias activas asociadas.',
                409,
                $e
            );
        }

        // Si no es un error de integridad conocido, propagar el error
        throw $e;
    }
}
