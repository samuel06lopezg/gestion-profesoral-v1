<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Programa;
use ApiGestion\Repositorios\IRepositorioPrograma;
use InvalidArgumentException;

class ServicioPrograma implements IServicioPrograma
{
    /**
     * Inyección de Dependencias por Constructor (PHP 8.0+).
     * El servicio solo conoce la INTERFAZ, nunca la implementación concreta.
     */
    public function __construct(private IRepositorioPrograma $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException("El límite debe ser un entero mayor que cero.");
        }

        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): Programa
    {
        $programa = $this->repositorio->obtenerPorId($id);

        if (!$programa) {
            throw new NoEncontradoExcepcion("No existe el programa con id = {$id}");
        }

        return $programa;
    }

    public function crear(array $datos): bool
    {
        $programa = new Programa(
            id: (int)$datos['id'],
            nombre: $datos['nombre'],
            tipo: $datos['tipo'],
            nivel: $datos['nivel'],
            fechaCreacion: $datos['fecha_creacion'],
            numeroCohortes: $datos['numero_cohortes'],
            cantGraduados: $datos['cant_graduados'],
            fechaActualizacion: $datos['fecha_actualizacion'],
            ciudad: $datos['ciudad'],
            facultad: (int)$datos['facultad'],
            fechaCierre: $datos['fecha_cierre'] ?? null
        );

        return $this->repositorio->crear($programa);
    }

    public function reemplazar(int $id, array $datos): bool
    {
        // Verifica primero la existencia (RF4 / 6_contracts.md)
        $this->obtenerPorId($id);

        $programa = new Programa(
            id: $id,
            nombre: $datos['nombre'],
            tipo: $datos['tipo'],
            nivel: $datos['nivel'],
            fechaCreacion: $datos['fecha_creacion'],
            numeroCohortes: $datos['numero_cohortes'],
            cantGraduados: $datos['cant_graduados'],
            fechaActualizacion: $datos['fecha_actualizacion'],
            ciudad: $datos['ciudad'],
            facultad: (int)$datos['facultad'],
            fechaCierre: $datos['fecha_cierre'] ?? null
        );

        return $this->repositorio->actualizar($programa);
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        // Regla RF5: Cuerpo vacío en PATCH responde 400 (InvalidArgumentException)
        if (empty($datos)) {
            throw new InvalidArgumentException("El cuerpo de la actualización parcial no puede estar vacío.");
        }

        // Verifica existencia previa
        $this->obtenerPorId($id);

        return $this->repositorio->actualizarParcial($id, $datos);
    }

    public function retirar(int $id): bool
    {
        // Regla RF6: Un segundo DELETE responde 404 (NoEncontradoExcepcion)
        $this->obtenerPorId($id);

        return $this->repositorio->eliminar($id);
    }
}