<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\RedDocente;
use ApiGestion\Repositorios\IRepositorioRedDocente;
use InvalidArgumentException;

class ServicioRedDocente implements IServicioRedDocente
{
    public function __construct(private IRepositorioRedDocente $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorClaves(int $red, int $docente): RedDocente
    {
        $rd = $this->repositorio->obtenerPorClaves($red, $docente);
        if (!$rd) {
            throw new NoEncontradoExcepcion("No existe la vinculación a la red {$red} para el docente {$docente}.");
        }
        return $rd;
    }

    public function crear(array $datos): bool
    {
        $rd = new RedDocente(
            red: (int)$datos['red'],
            docente: (int)$datos['docente'],
            fechaInicio: (string)$datos['fecha_inicio'],
            fechaFin: !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null,
            actDestacadas: (string)($datos['act_destacadas'] ?? '')
        );

        return $this->repositorio->crear($rd);
    }

    public function reemplazar(int $red, int $docente, array $datos): bool
    {
        $this->obtenerPorClaves($red, $docente);

        $rd = new RedDocente(
            red: $red,
            docente: $docente,
            fechaInicio: (string)$datos['fecha_inicio'],
            fechaFin: !empty($datos['fecha_fin']) ? (string)$datos['fecha_fin'] : null,
            actDestacadas: (string)($datos['act_destacadas'] ?? '')
        );

        return $this->repositorio->actualizar($rd);
    }

    public function actualizarParcial(int $red, int $docente, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorClaves($red, $docente);
        return $this->repositorio->actualizarParcial($red, $docente, $datos);
    }

    public function retirar(int $red, int $docente): bool
    {
        $this->obtenerPorClaves($red, $docente);
        return $this->repositorio->eliminar($red, $docente);
    }
}
