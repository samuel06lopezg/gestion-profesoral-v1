<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

class DocenteDepartamento
{
    public function __construct(
        public int $docente,
        public int $departamento,
        public string $dedicacion,
        public string $modalidad,
        public string $fechaIngreso,
        public ?string $fechaSalida = null,
        public ?string $docenteNombre = null,
        public ?string $departamentoNombre = null
    ) {}

    public function toArray(): array
    {
        return [
            'docente'             => $this->docente,
            'departamento'        => $this->departamento,
            'dedicacion'          => $this->dedicacion,
            'modalidad'           => $this->modalidad,
            'fecha_ingreso'       => $this->fechaIngreso,
            'fecha_salida'        => $this->fechaSalida,
            'docente_nombre'      => $this->docenteNombre,
            'departamento_nombre' => $this->departamentoNombre
        ];
    }
}
