<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class InteresFuturo
{
    public function __construct(
        public int $docente,
        public string $terminoClave,
        public ?string $docenteNombre = null
    ) {}

    public function toArray(): array
    {
        return [
            'docente'        => $this->docente,
            'termino_clave'  => $this->terminoClave,
            'docente_nombre' => $this->docenteNombre
        ];
    }
}
