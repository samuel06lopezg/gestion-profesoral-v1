<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Entidad de dominio Rol para control de acceso RBAC.
 */
class Rol
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public ?string $descripcion = null,
        public int $activo = 1,
        public ?string $fechaCreacion = null
    ) {
    }

    /**
     * @param array<string, mixed> $datos
     */
    public static function desdeArreglo(array $datos): self
    {
        return new self(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            nombre: (string) ($datos['nombre'] ?? ''),
            descripcion: isset($datos['descripcion']) ? (string) $datos['descripcion'] : null,
            activo: isset($datos['activo']) ? (int) $datos['activo'] : 1,
            fechaCreacion: isset($datos['fecha_creacion']) ? (string) $datos['fecha_creacion'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'nombre'         => $this->nombre,
            'descripcion'    => $this->descripcion,
            'activo'         => $this->activo,
            'fecha_creacion' => $this->fechaCreacion,
        ];
    }
}
