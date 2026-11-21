<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Entidad de dominio Usuario con roles asignados.
 */
class Usuario
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public ?int $id,
        public string $username,
        public string $password,
        public string $email,
        public ?string $nombreCompleto = null,
        public int $activo = 1,
        public ?string $fechaCreacion = null,
        public ?string $fechaActualizacion = null,
        public array $roles = []
    ) {
    }

    /**
     * @param array<string, mixed> $datos
     * @param string[]             $roles
     */
    public static function desdeArreglo(array $datos, array $roles = []): self
    {
        return new self(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            username: (string) ($datos['username'] ?? ''),
            password: (string) ($datos['password'] ?? ''),
            email: (string) ($datos['email'] ?? ''),
            nombreCompleto: isset($datos['nombre_completo']) ? (string) $datos['nombre_completo'] : null,
            activo: isset($datos['activo']) ? (int) $datos['activo'] : 1,
            fechaCreacion: isset($datos['fecha_creacion']) ? (string) $datos['fecha_creacion'] : null,
            fechaActualizacion: isset($datos['fecha_actualizacion']) ? (string) $datos['fecha_actualizacion'] : null,
            roles: $roles
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $incluirClave = false): array
    {
        $salida = [
            'id'                  => $this->id,
            'username'            => $this->username,
            'email'               => $this->email,
            'nombre_completo'     => $this->nombreCompleto,
            'activo'              => $this->activo,
            'fecha_creacion'      => $this->fechaCreacion,
            'fecha_actualizacion' => $this->fechaActualizacion,
            'roles'               => $this->roles,
        ];

        if ($incluirClave) {
            $salida['password'] = $this->password;
        }

        return $salida;
    }
}
