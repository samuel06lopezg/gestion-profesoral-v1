<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa la ficha de un Estudio Realizado por un Docente (Detalle).
 */
class EstudiosRealizados
{
    public function __construct(
        public int $id,
        public string $titulo,
        public string $universidad,
        public string $fecha,
        public string $tipo,
        public string $ciudad,
        public int $docente,
        public int $insAcreditada,
        public string $metodologia,
        public string $perfilEgresado,
        public string $pais
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'titulo'          => $this->titulo,
            'universidad'     => $this->universidad,
            'fecha'           => $this->fecha,
            'tipo'            => $this->tipo,
            'ciudad'          => $this->ciudad,
            'docente'         => $this->docente,
            'ins_acreditada'  => $this->insAcreditada,
            'metodologia'     => $this->metodologia,
            'perfil_egresado' => $this->perfilEgresado,
            'pais'            => $this->pais
        ];
    }
}
