<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\AreaConocimiento;
use ApiGestion\Repositorios\IRepositorioAreaConocimiento;
use InvalidArgumentException;

class ServicioAreaConocimiento implements IServicioAreaConocimiento
{
    public function __construct(private IRepositorioAreaConocimiento $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(string $id): AreaConocimiento
    {
        $area = $this->repositorio->obtenerPorId($id);
        if (!$area) {
            throw new NoEncontradoExcepcion("No existe el área de conocimiento con id = {$id}");
        }
        return $area;
    }

    public function crear(array $datos): bool
    {
        $area = new AreaConocimiento(
            id: (string)$datos['id'],
            granArea: $datos['gran_area'],
            area: $datos['area'],
            disciplina: $datos['disciplina']
        );
        return $this->repositorio->crear($area);
    }

    public function reemplazar(string $id, array $datos): bool
    {
        $this->obtenerPorId($id);

        $area = new AreaConocimiento(
            id: $id,
            granArea: $datos['gran_area'],
            area: $datos['area'],
            disciplina: $datos['disciplina']
        );
        return $this->repositorio->actualizar($area);
    }

    public function actualizarParcial(string $id, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorId($id);
        return $this->repositorio->actualizarParcial($id, $datos);
    }

    public function retirar(string $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->eliminar($id);
    }
}