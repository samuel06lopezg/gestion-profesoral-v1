<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class TerminoClave
{
    public function __construct(
        public string $termino,
        public ?string $terminoIngles = null
    ) {}

    public function toArray(): array
    {
        return [
            'termino'        => $this->termino,
            'termino_ingles' => $this->terminoIngles
        ];
    }
}