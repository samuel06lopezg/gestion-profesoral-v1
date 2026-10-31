<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class RedDocente
{
    public function __construct(
        public int $red,
        public int $docente,
        public string $fechaInicio,
        public ?string $fechaFin = null,
        public string $actDestacadas = '',
        public ?string $redNombre = null,
        public ?string $docenteNombre = null
    ) {}

    public function toArray(): array
    {
        return [
            'red'            => $this->red,
            'docente'        => $this->docente,
            'fecha_inicio'   => $this->fechaInicio,
            'fecha_fin'      => $this->fechaFin,
            'act_destacadas' => $this->actDestacadas,
            'red_nombre'     => $this->redNombre,
            'docente_nombre' => $this->docenteNombre
        ];
    }
}
