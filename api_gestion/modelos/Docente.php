<?php
declare(strict_types=1);

namespace ApiGestion\Modelos;

/**
 * Representa la ficha de un Docente (Maestro).
 */
class Docente
{
    /**
     * @param array<int, mixed> $estudios
     */
    public function __construct(
        public int $cedula,
        public string $nombres,
        public string $apellidos,
        public string $genero,
        public string $cargo,
        public string $fechaNacimiento,
        public string $correo,
        public string $telefono,
        public string $urlCvlac,
        public string $fechaActualizacion,
        public string $escalafon,
        public string $perfil,
        public string $convMinciencia,
        public string $nacionalidaad,
        public ?string $catMinciencia = null,
        public ?int $lineaInvestigacionPrincipal = null,
        public array $estudios = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $salida = [
            'cedula'                        => $this->cedula,
            'nombres'                       => $this->nombres,
            'apellidos'                     => $this->apellidos,
            'genero'                        => $this->genero,
            'cargo'                         => $this->cargo,
            'fecha_nacimiento'              => $this->fechaNacimiento,
            'correo'                        => $this->correo,
            'telefono'                      => $this->telefono,
            'url_cvlac'                     => $this->urlCvlac,
            'fecha_actualizacion'           => $this->fechaActualizacion,
            'escalafon'                     => $this->escalafon,
            'perfil'                        => $this->perfil,
            'cat_minciencia'                => $this->catMinciencia,
            'conv_minciencia'               => $this->convMinciencia,
            'nacionalidaad'                 => $this->nacionalidaad,
            'linea_investigacion_principal' => $this->lineaInvestigacionPrincipal
        ];

        if (!empty($this->estudios)) {
            $salida['estudios'] = array_map(
                fn($e) => $e instanceof EstudiosRealizados ? $e->toArray() : $e,
                $this->estudios
            );
        }

        return $salida;
    }
}
