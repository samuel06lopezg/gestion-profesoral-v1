<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class Beca
{
    public function __construct(
        public int $estudios,
        public string $tipo,
        public string $institucion,
        public string $fechaInicio,
        public ?string $fechaFin = null,
        public ?string $estudiosTitulo = null
    ) {}

    public function toArray(): array
    {
        return [
            'estudios'        => $this->estudios,
            'tipo'            => $this->tipo,
            'institucion'     => $this->institucion,
            'fecha_inicio'    => $this->fechaInicio,
            'fecha_fin'       => $this->fechaFin,
            'estudios_titulo' => $this->estudiosTitulo
        ];
    }
}
