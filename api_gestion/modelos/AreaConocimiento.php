<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class AreaConocimiento
{
    public function __construct(
        public string $id,
        public string $granArea,
        public string $area,
        public string $disciplina
    ) {}

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'gran_area' => $this->granArea,
            'area'      => $this->area,
            'disciplina'=> $this->disciplina
        ];
    }
}