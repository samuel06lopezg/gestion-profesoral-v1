<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\InteresFuturo;
use ApiGestion\Repositorios\IRepositorioInteresFuturo;
use InvalidArgumentException;

class ServicioInteresFuturo implements IServicioInteresFuturo
{
    public function __construct(private IRepositorioInteresFuturo $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorClaves(int $docente, string $terminoClave): InteresFuturo
    {
        $interes = $this->repositorio->obtenerPorClaves($docente, $terminoClave);
        if (!$interes) {
            throw new NoEncontradoExcepcion("No existe el interés de investigación para el docente {$docente} con término {$terminoClave}.");
        }
        return $interes;
    }

    public function crear(array $datos): bool
    {
        $interes = new InteresFuturo(
            docente: (int)$datos['docente'],
            terminoClave: (string)$datos['termino_clave']
        );

        return $this->repositorio->crear($interes);
    }

    public function retirar(int $docente, string $terminoClave): bool
    {
        $this->obtenerPorClaves($docente, $terminoClave);
        return $this->repositorio->eliminar($docente, $terminoClave);
    }
}
