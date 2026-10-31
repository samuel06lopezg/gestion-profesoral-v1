<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa una distinción o reconocimiento otorgado a un docente.
 */
class Reconocimiento
{
    public function __construct(
        public int $id,
        public string $tipo,
        public string $fecha,
        public string $institucion,
        public string $nombre,
        public string $ambito,
        public int $docente
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tipo'        => $this->tipo,
            'fecha'       => $this->fecha,
            'institucion' => $this->institucion,
            'nombre'      => $this->nombre,
            'ambito'      => $this->ambito,
            'docente'     => $this->docente
        ];
    }
}
