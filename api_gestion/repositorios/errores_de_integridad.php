<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use PDOException;

/**
 * Mapea excepciones de integridad referencial y reglas de negocio SQL a ConflictoIntegridadExcepcion (HTTP 409).
 */
function mapearErrorIntegridad(PDOException $e): ?ConflictoIntegridadExcepcion
{
    $sqlState = (string)($e->errorInfo[0] ?? '');
    $errno    = (int)($e->errorInfo[1] ?? 0);
    $mensaje  = (string)($e->errorInfo[2] ?? $e->getMessage());

    // Regla de negocio disparada por SIGNAL SQLSTATE '45000' en procedimientos almacenados
    if ($sqlState === '45000') {
        return new ConflictoIntegridadExcepcion($mensaje, 409, $e);
    }

    // 1452: Clave foránea no encontrada (padre inexistente)
    if ($errno === 1452) {
        return new ConflictoIntegridadExcepcion('No se puede guardar el registro porque la clave foránea no existe en el sistema.', 409, $e);
    }

    // 1062: Entrada duplicada (clave primaria o única repetida)
    if ($errno === 1062) {
        return new ConflictoIntegridadExcepcion('Ya existe un registro con la misma clave en el sistema.', 409, $e);
    }

    // 1451: Restricción de clave foránea al borrar
    if ($errno === 1451) {
        return new ConflictoIntegridadExcepcion('No se puede eliminar el registro porque tiene datos asociados.', 409, $e);
    }

    return null;
}
