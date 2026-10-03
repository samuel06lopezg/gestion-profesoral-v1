<?php
declare(strict_types=1);

namespace ApiGestion\Excepciones;

use Exception;

/**
 * Excepción lanzada cuando un registro no existe en la base de datos
 * o ha sido retirado (borrado lógico).
 */
class NoEncontradoExcepcion extends Exception
{
}