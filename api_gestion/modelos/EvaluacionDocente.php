<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa la calificación semestral de un docente.
 */
class EvaluacionDocente
{
    public function __construct(
        public int $id,
        public float $calificacion,
        public string $semestre,
        public int $docente
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'calificacion' => $this->calificacion,
            'semestre'     => $this->semestre,
            'docente'      => $this->docente
        ];
    }
}
