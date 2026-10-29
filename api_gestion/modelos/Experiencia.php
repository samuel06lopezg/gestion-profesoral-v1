<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa la trayectoria laboral de un docente (tabla experiecia).
 */
class Experiencia
{
    public function __construct(
        public int $id,
        public string $nombreCargo,
        public string $institucion,
        public string $tipo,
        public string $fechaInicio,
        public int $docente,
        public ?string $fechaFin = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'nombre_cargo' => $this->nombreCargo,
            'institucion'  => $this->institucion,
            'tipo'         => $this->tipo,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin'    => $this->fechaFin,
            'docente'      => $this->docente
        ];
    }
}
