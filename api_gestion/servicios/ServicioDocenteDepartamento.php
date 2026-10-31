<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\DocenteDepartamento;
use ApiGestion\Repositorios\IRepositorioDocenteDepartamento;
use InvalidArgumentException;

class ServicioDocenteDepartamento implements IServicioDocenteDepartamento
{
    public function __construct(private IRepositorioDocenteDepartamento $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorClaves(int $docente, int $departamento): DocenteDepartamento
    {
        $dd = $this->repositorio->obtenerPorClaves($docente, $departamento);
        if (!$dd) {
            throw new NoEncontradoExcepcion("No existe la vinculación docente {$docente} con departamento {$departamento}.");
        }
        return $dd;
    }

    public function crear(array $datos): bool
    {
        $dd = new DocenteDepartamento(
            docente: (int)$datos['docente'],
            departamento: (int)$datos['departamento'],
            dedicacion: (string)$datos['dedicacion'],
            modalidad: (string)$datos['modalidad'],
            fechaIngreso: (string)$datos['fecha_ingreso'],
            fechaSalida: !empty($datos['fecha_salida']) ? (string)$datos['fecha_salida'] : null
        );

        return $this->repositorio->crear($dd);
    }

    public function reemplazar(int $docente, int $departamento, array $datos): bool
    {
        $this->obtenerPorClaves($docente, $departamento);

        $dd = new DocenteDepartamento(
            docente: $docente,
            departamento: $departamento,
            dedicacion: (string)$datos['dedicacion'],
            modalidad: (string)$datos['modalidad'],
            fechaIngreso: (string)$datos['fecha_ingreso'],
            fechaSalida: !empty($datos['fecha_salida']) ? (string)$datos['fecha_salida'] : null
        );

        return $this->repositorio->actualizar($dd);
    }

    public function actualizarParcial(int $docente, int $departamento, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorClaves($docente, $departamento);
        return $this->repositorio->actualizarParcial($docente, $departamento, $datos);
    }

    public function retirar(int $docente, int $departamento): bool
    {
        $this->obtenerPorClaves($docente, $departamento);
        return $this->repositorio->eliminar($docente, $departamento);
    }
}
