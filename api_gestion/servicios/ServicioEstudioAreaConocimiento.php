<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\EstudioAreaConocimiento;
use ApiGestion\Repositorios\IRepositorioEstudioAreaConocimiento;
use InvalidArgumentException;

class ServicioEstudioAreaConocimiento implements IServicioEstudioAreaConocimiento
{
    public function __construct(private IRepositorioEstudioAreaConocimiento $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorClaves(int $estudio, string $areaConocimiento): EstudioAreaConocimiento
    {
        $eac = $this->repositorio->obtenerPorClaves($estudio, $areaConocimiento);
        if (!$eac) {
            throw new NoEncontradoExcepcion("No existe la asociación entre el estudio {$estudio} y el área de conocimiento {$areaConocimiento}.");
        }
        return $eac;
    }

    public function crear(array $datos): bool
    {
        $eac = new EstudioAreaConocimiento(
            estudio: (int)$datos['estudio'],
            areaConocimiento: (string)$datos['area_conocimiento']
        );

        return $this->repositorio->crear($eac);
    }

    public function retirar(int $estudio, string $areaConocimiento): bool
    {
        $this->obtenerPorClaves($estudio, $areaConocimiento);
        return $this->repositorio->eliminar($estudio, $areaConocimiento);
    }
}
