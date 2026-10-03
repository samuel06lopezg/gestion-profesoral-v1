<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Red;
use ApiGestion\Repositorios\IRepositorioRed;
use InvalidArgumentException;

// El servicio aplica las REGLAS. No sabe de HTTP (nada de 404 ni 422)
// y no escribe SQL: para eso está el repositorio.
class ServicioRed implements IServicioRed
{
    // Recibe la INTERFAZ del repositorio, no la clase concreta.
    // "private" en el constructor crea y guarda la propiedad (PHP 8).
    public function __construct(private IRepositorioRed $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }
        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): Red
    {
        $red = $this->repositorio->obtenerPorId($id);
        if (!$red) {
            throw new NoEncontradoExcepcion("No existe la red con idr = {$id}");
        }
        return $red;
    }

    public function crear(array $datos): bool
    {
        $red = new Red(
            idr: (int)$datos['idr'],
            nombre: $datos['nombre'],
            url: $datos['url'],
            pais: $datos['pais']
        );
        return $this->repositorio->crear($red);
    }

    public function reemplazar(int $id, array $datos): bool
    {
        $this->obtenerPorId($id); // si no existe, lanza NoEncontradoExcepcion

        $red = new Red(
            idr: $id,
            nombre: $datos['nombre'],
            url: $datos['url'],
            pais: $datos['pais']
        );
        return $this->repositorio->actualizar($red);
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        // Regla: un PATCH con cuerpo vacío no tiene sentido
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