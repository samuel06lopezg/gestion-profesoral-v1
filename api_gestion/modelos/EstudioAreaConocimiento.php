<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class EstudioAreaConocimiento
{
    public function __construct(
        public int $estudio,
        public string $areaConocimiento,
        public ?string $estudioTitulo = null,
        public ?string $areaDisciplina = null
    ) {}

    public function toArray(): array
    {
        return [
            'estudio'           => $this->estudio,
            'area_conocimiento' => $this->areaConocimiento,
            'estudio_titulo'    => $this->estudioTitulo,
            'area_disciplina'   => $this->areaDisciplina
        ];
    }
}
