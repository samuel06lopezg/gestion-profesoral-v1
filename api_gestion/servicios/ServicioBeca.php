<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Beca;
use ApiGestion\Repositorios\IRepositorioBeca;
use InvalidArgumentException;

class ServicioBeca implements IServicioBeca
{
    public function __construct(private IRepositorioBeca $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $estudios): Beca
    {
        $beca = $this->repositorio->obtenerPorId($estudios);
        if (!$beca) {
            throw new NoEncontradoExcepcion("No existe beca registrada para el estudio {$estudios}.");
        }
        return $beca;
    }

    public function crear(array $datos): bool
    {
        $beca = new Beca(
            estudios: (int)$datos['estudios'],
            tipo: (string)$datos['tipo'],
            institucion: (string)$datos['institucion'],
            fechaInicio: (string)$datos['fecha_inicio'],
            fechaFin: !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null
        );

        return $this->repositorio->crear($beca);
    }

    public function reemplazar(int $estudios, array $datos): bool
    {
        $this->obtenerPorId($estudios);

        $beca = new Beca(
            estudios: $estudios,
            tipo: (string)$datos['tipo'],
            institucion: (string)$datos['institucion'],
            fechaInicio: (string)$datos['fecha_inicio'],
            fechaFin: !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null
        );

        return $this->repositorio->actualizar($beca);
    }

    public function actualizarParcial(int $estudios, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorId($estudios);
        return $this->repositorio->actualizarParcial($estudios, $datos);
    }

    public function retirar(int $estudios): bool
    {
        $this->obtenerPorId($estudios);
        return $this->repositorio->eliminar($estudios);
    }
}
