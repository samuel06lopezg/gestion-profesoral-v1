<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa la ficha de un Programa Académico.
 * 
 * Cumple con la especificación de 2_spec.md §3:
 * Contiene 11 campos (10 obligatorios y 1 opcional: fecha_cierre).
 * El campo 'activo' NO pertenece al modelo de la ficha hacia la API.
 */
class Programa
{
    /**
     * Promoción de propiedades en el constructor (Sintaxis PHP 8.0+).
     * Explica tipos nulos (?string) para campos opcionales.
     */
    public function __construct(
        public int $id,
        public string $nombre,
        public string $tipo,
        public string $nivel,
        public string $fechaCreacion,
        public string $numeroCohortes,
        public string $cantGraduados,
        public string $fechaActualizacion,
        public string $ciudad,
        public int $facultad,
        public ?string $fechaCierre = null
    ) {}

    /**
     * Convierte la instancia en un arreglo asociativo para la respuesta JSON.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'nombre'              => $this->nombre,
            'tipo'                => $this->tipo,
            'nivel'               => $this->nivel,
            'fecha_creacion'      => $this->fechaCreacion,
            'numero_cohortes'     => $this->numeroCohortes,
            'cant_graduados'      => $this->cantGraduados,
            'fecha_actualizacion' => $this->fechaActualizacion,
            'ciudad'              => $this->ciudad,
            'facultad'            => $this->facultad,
            'fecha_cierre'        => $this->fechaCierre
        ];
    }
}