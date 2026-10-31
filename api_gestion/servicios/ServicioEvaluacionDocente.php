<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\EvaluacionDocente;
use ApiGestion\Repositorios\IRepositorioEvaluacionDocente;
use InvalidArgumentException;

class ServicioEvaluacionDocente implements IServicioEvaluacionDocente
{
    public function __construct(private IRepositorioEvaluacionDocente $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }

        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): EvaluacionDocente
    {
        $evaluacion = $this->repositorio->obtenerPorId($id);
        if (!$evaluacion) {
            throw new NoEncontradoExcepcion("No existe la evaluación con id {$id}.");
        }

        return $evaluacion;
    }

    public function crear(array $datos): EvaluacionDocente
    {
        return $this->repositorio->crear($datos);
    }

    public function reemplazar(int $id, array $datos): EvaluacionDocente
    {
        $this->obtenerPorId($id);

        $evaluacion = $this->repositorio->actualizar($id, $datos);
        if (!$evaluacion) {
            throw new NoEncontradoExcepcion("No se pudo actualizar la evaluación con id {$id}.");
        }

        return $evaluacion;
    }

    public function actualizarParcial(int $id, array $datos): EvaluacionDocente
    {
        $this->obtenerPorId($id);

        $evaluacion = $this->repositorio->actualizarParcial($id, $datos);
        if (!$evaluacion) {
            throw new NoEncontradoExcepcion("No se pudo actualizar la evaluación con id {$id}.");
        }

        return $evaluacion;
    }

    public function retirar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->retirar($id);
    }
}
