<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\TerminoClave;
use ApiGestion\Repositorios\IRepositorioTerminoClave;
use InvalidArgumentException;

class ServicioTerminoClave implements IServicioTerminoClave
{
    public function __construct(private IRepositorioTerminoClave $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorTermino(string $termino): TerminoClave
    {
        $encontrado = $this->repositorio->obtenerPorTermino($termino);
        if (!$encontrado) {
            throw new NoEncontradoExcepcion("No existe el término clave «{$termino}»");
        }
        return $encontrado;
    }

    public function crear(array $datos): bool
    {
        $termino = new TerminoClave($datos['termino'], $this->aNulo($datos['termino_ingles'] ?? null));
        return $this->repositorio->crear($termino);
    }

    public function reemplazar(string $termino, array $datos): bool
    {
        $this->obtenerPorTermino($termino);

        $nuevo = new TerminoClave($termino, $this->aNulo($datos['termino_ingles'] ?? null));
        return $this->repositorio->actualizar($nuevo);
    }

    public function actualizarParcial(string $termino, array $datos): bool
    {
        if (empty($datos)) {
            throw new InvalidArgumentException('El cuerpo de la actualización parcial no puede estar vacío.');
        }

        $this->obtenerPorTermino($termino);

        if (array_key_exists('termino_ingles', $datos)) {
            $datos['termino_ingles'] = $this->aNulo($datos['termino_ingles']);
        }
        return $this->repositorio->actualizarParcial($termino, $datos);
    }

    public function retirar(string $termino): bool
    {
        $this->obtenerPorTermino($termino);
        return $this->repositorio->eliminar($termino);
    }

    // Regla: un campo opcional vacío se guarda como NULL, no como texto vacío.
    private function aNulo(mixed $valor): ?string
    {
        return ($valor === null || $valor === '') ? null : (string)$valor;
    }
}