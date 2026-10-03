<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class Red
{
    public function __construct(
        public int $idr,
        public string $nombre,
        public string $url,
        public string $pais
    ) {}

    public function toArray(): array
    {
        return [
            'idr'    => $this->idr,
            'nombre' => $this->nombre,
            'url'    => $this->url,
            'pais'   => $this->pais
        ];
    }
}