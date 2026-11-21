<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Entidad de asignación Usuario-Rol (tabla puente).
 */
class RolUsuario
{
    public function __construct(
        public int $usuarioId,
        public int $rolId,
        public ?string $nombreRol = null
    ) {
    }

    /**
     * @param array<string, mixed> $datos
     */
    public static function desdeArreglo(array $datos): self
    {
        return new self(
            usuarioId: (int) ($datos['usuario_id'] ?? 0),
            rolId: (int) ($datos['rol_id'] ?? 0),
            nombreRol: isset($datos['nombre_rol']) ? (string) $datos['nombre_rol'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'usuario_id' => $this->usuarioId,
            'rol_id'     => $this->rolId,
            'nombre_rol' => $this->nombreRol,
        ];
    }
}
