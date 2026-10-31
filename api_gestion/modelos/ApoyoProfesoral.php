<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class ApoyoProfesoral
{
    public function __construct(
        public int $estudios,
        public int $conApoyo,
        public string $institucion,
        public string $tipo,
        public ?string $estudiosTitulo = null
    ) {}

    public function toArray(): array
    {
        return [
            'estudios'        => $this->estudios,
            'con_apoyo'       => $this->conApoyo,
            'institucion'     => $this->institucion,
            'tipo'            => $this->tipo,
            'estudios_titulo' => $this->estudiosTitulo
        ];
    }
}
