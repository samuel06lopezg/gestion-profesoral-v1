<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Experiencia;
use ApiGestion\Repositorios\IRepositorioExperiencia;
use InvalidArgumentException;

class ServicioExperiencia implements IServicioExperiencia
{
    public function __construct(private IRepositorioExperiencia $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }

        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): Experiencia
    {
        $experiencia = $this->repositorio->obtenerPorId($id);
        if (!$experiencia) {
            throw new NoEncontradoExcepcion("No existe la experiencia con id {$id}.");
        }

        return $experiencia;
    }

    public function crear(array $datos): Experiencia
    {
        return $this->repositorio->crear($datos);
    }

    public function reemplazar(int $id, array $datos): Experiencia
    {
        $this->obtenerPorId($id);

        $experiencia = $this->repositorio->actualizar($id, $datos);
        if (!$experiencia) {
            throw new NoEncontradoExcepcion("No se pudo actualizar la experiencia con id {$id}.");
        }

        return $experiencia;
    }

    public function actualizarParcial(int $id, array $datos): Experiencia
    {
        $this->obtenerPorId($id);

        $experiencia = $this->repositorio->actualizarParcial($id, $datos);
        if (!$experiencia) {
            throw new NoEncontradoExcepcion("No se pudo actualizar la experiencia con id {$id}.");
        }

        return $experiencia;
    }

    public function retirar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->retirar($id);
    }
}
