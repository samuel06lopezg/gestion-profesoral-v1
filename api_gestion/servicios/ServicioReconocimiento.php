<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Reconocimiento;
use ApiGestion\Repositorios\IRepositorioReconocimiento;
use InvalidArgumentException;

class ServicioReconocimiento implements IServicioReconocimiento
{
    public function __construct(private IRepositorioReconocimiento $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }

        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): Reconocimiento
    {
        $reconocimiento = $this->repositorio->obtenerPorId($id);
        if (!$reconocimiento) {
            throw new NoEncontradoExcepcion("No existe el reconocimiento con id {$id}.");
        }

        return $reconocimiento;
    }

    public function crear(array $datos): Reconocimiento
    {
        return $this->repositorio->crear($datos);
    }

    public function reemplazar(int $id, array $datos): Reconocimiento
    {
        $this->obtenerPorId($id);

        $reconocimiento = $this->repositorio->actualizar($id, $datos);
        if (!$reconocimiento) {
            throw new NoEncontradoExcepcion("No se pudo actualizar el reconocimiento con id {$id}.");
        }

        return $reconocimiento;
    }

    public function actualizarParcial(int $id, array $datos): Reconocimiento
    {
        $this->obtenerPorId($id);

        $reconocimiento = $this->repositorio->actualizarParcial($id, $datos);
        if (!$reconocimiento) {
            throw new NoEncontradoExcepcion("No se pudo actualizar el reconocimiento con id {$id}.");
        }

        return $reconocimiento;
    }

    public function retirar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->retirar($id);
    }
}
