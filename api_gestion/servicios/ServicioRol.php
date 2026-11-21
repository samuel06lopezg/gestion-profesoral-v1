<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Rol;
use ApiGestion\Repositorios\IRepositorioRol;
use PDOException;

class ServicioRol implements IServicioRol
{
    public function __construct(
        private readonly IRepositorioRol $repositorio
    ) {
    }

    public function obtenerTodos(): array
    {
        return $this->repositorio->obtenerTodos();
    }

    public function obtenerPorId(int $id): Rol
    {
        $rol = $this->repositorio->obtenerPorId($id);
        if ($rol === null) {
            throw new NoEncontradoExcepcion("No se encontro el rol con ID {$id}.");
        }
        return $rol;
    }

    public function crear(array $datos): Rol
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        if ($nombre === '') {
            throw new ConflictoIntegridadExcepcion("El nombre del rol es obligatorio.");
        }

        $existente = $this->repositorio->obtenerPorNombre($nombre);
        if ($existente !== null) {
            throw new ConflictoIntegridadExcepcion("Ya existe un rol con el nombre '{$nombre}'.");
        }

        $rol = new Rol(
            id: null,
            nombre: strtolower($nombre),
            descripcion: isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : null,
            activo: 1
        );

        try {
            $nuevo = $this->repositorio->crear($rol);
            if ($nuevo === null) {
                throw new ConflictoIntegridadExcepcion("No se pudo registrar el rol.");
            }
            return $nuevo;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                throw new ConflictoIntegridadExcepcion("Ya existe un rol con el nombre '{$nombre}'.");
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): Rol
    {
        $rol = $this->obtenerPorId($id);

        if (isset($datos['nombre'])) {
            $nombre = strtolower(trim((string) $datos['nombre']));
            if ($nombre === '') {
                throw new ConflictoIntegridadExcepcion("El nombre del rol no puede quedar vacio.");
            }
            $rol->nombre = $nombre;
        }

        if (array_key_exists('descripcion', $datos)) {
            $rol->descripcion = $datos['descripcion'] !== null ? trim((string) $datos['descripcion']) : null;
        }

        $this->repositorio->actualizar($rol);
        return $this->obtenerPorId($id);
    }

    public function eliminar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->eliminar($id);
    }
}
