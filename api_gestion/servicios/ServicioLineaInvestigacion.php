<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\LineaInvestigacion;
use ApiGestion\Repositorios\IRepositorioLineaInvestigacion;
use InvalidArgumentException;

class ServicioLineaInvestigacion implements IServicioLineaInvestigacion
{
    public function __construct(private IRepositorioLineaInvestigacion $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): LineaInvestigacion
    {
        $linea = $this->repositorio->obtenerPorId($id);
        if (!$linea) {
            throw new NoEncontradoExcepcion("No existe la línea de investigación con id = {$id}");
        }
        return $linea;
    }

    public function crear(array $datos): int
    {
        // id = 0 es solo un relleno: el modelo lo exige, pero el repositorio
        // NO lo inserta; la base genera el verdadero.
        $linea = new LineaInvestigacion(0, $datos['nombre'], $datos['descripcion']);
        return $this->repositorio->crear($linea);
    }

    public function reemplazar(int $id, array $datos): bool
    {
        $this->obtenerPorId($id);

        $linea = new LineaInvestigacion($id, $datos['nombre'], $datos['descripcion']);
        return $this->repositorio->actualizar($linea);
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorId($id);
        return $this->repositorio->actualizarParcial($id, $datos);
    }

    public function retirar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->eliminar($id);
    }
}