<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class LineaInvestigacion
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $descripcion
    ) {}

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }
}