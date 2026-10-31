<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\ApoyoProfesoral;
use ApiGestion\Repositorios\IRepositorioApoyoProfesoral;
use InvalidArgumentException;

class ServicioApoyoProfesoral implements IServicioApoyoProfesoral
{
    public function __construct(private IRepositorioApoyoProfesoral $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $estudios): ApoyoProfesoral
    {
        $apoyo = $this->repositorio->obtenerPorId($estudios);
        if (!$apoyo) {
            throw new NoEncontradoExcepcion("No existe apoyo profesoral registrado para el estudio {$estudios}.");
        }
        return $apoyo;
    }

    public function crear(array $datos): bool
    {
        $apoyo = new ApoyoProfesoral(
            estudios: (int)$datos['estudios'],
            conApoyo: (int)$datos['con_apoyo'],
            institucion: (string)$datos['institucion'],
            tipo: (string)$datos['tipo']
        );

        return $this->repositorio->crear($apoyo);
    }

    public function reemplazar(int $estudios, array $datos): bool
    {
        $this->obtenerPorId($estudios);

        $apoyo = new ApoyoProfesoral(
            estudios: $estudios,
            conApoyo: (int)$datos['con_apoyo'],
            institucion: (string)$datos['institucion'],
            tipo: (string)$datos['tipo']
        );

        return $this->repositorio->actualizar($apoyo);
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
