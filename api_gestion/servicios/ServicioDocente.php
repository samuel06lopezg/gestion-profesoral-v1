<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Docente;
use ApiGestion\Repositorios\IRepositorioDocente;
use ApiGestion\Repositorios\IRepositorioEstudiosRealizados;
use InvalidArgumentException;
use Throwable;

class ServicioDocente implements IServicioDocente
{
    public function __construct(
        private IRepositorioDocente $repoDocente,
        private IRepositorioEstudiosRealizados $repoEstudios
    ) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }

        return $this->repoDocente->obtenerTodos($limite);
    }

    public function obtenerPorCedula(int $cedula, bool $incluirEstudios = true): Docente
    {
        $docente = $this->repoDocente->obtenerPorCedula($cedula);
        if (!$docente) {
            throw new NoEncontradoExcepcion("No existe el docente con cédula {$cedula}.");
        }

        if ($incluirEstudios) {
            $docente->estudios = $this->repoEstudios->obtenerPorDocente($cedula);
        }

        return $docente;
    }

    public function crear(array $datos): Docente
    {
        $conexion = $this->repoDocente->obtenerConexion();
        $conDetalles = !empty($datos['estudios']) && is_array($datos['estudios']);

        if ($conDetalles) {
            $conexion->beginTransaction();
        }

        try {
            $docente = $this->repoDocente->crear($datos);

            if ($conDetalles) {
                $estudiosCreados = [];
                foreach ($datos['estudios'] as $estudioData) {
                    if (empty($estudioData['titulo'])) {
                        continue;
                    }
                    $estudioData['docente'] = $docente->cedula;
                    $estudiosCreados[] = $this->repoEstudios->crear($estudioData);
                }
                $conexion->commit();
                $docente->estudios = $estudiosCreados;
            }

            return $docente;
        } catch (Throwable $e) {
            if ($conDetalles && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    public function reemplazar(int $cedula, array $datos): Docente
    {
        $this->obtenerPorCedula($cedula, incluirEstudios: false);

        $docente = $this->repoDocente->actualizar($cedula, $datos);
        if (!$docente) {
            throw new NoEncontradoExcepcion("No se pudo actualizar el docente con cédula {$cedula}.");
        }

        return $docente;
    }

    public function actualizarParcial(int $cedula, array $datos): Docente
    {
        $actual = $this->obtenerPorCedula($cedula, incluirEstudios: false);

        $datosCompletos = [
            'nombres'                       => $datos['nombres'] ?? $actual->nombres,
            'apellidos'                     => $datos['apellidos'] ?? $actual->apellidos,
            'genero'                        => $datos['genero'] ?? $actual->genero,
            'cargo'                         => $datos['cargo'] ?? $actual->cargo,
            'fecha_nacimiento'              => $datos['fecha_nacimiento'] ?? $actual->fechaNacimiento,
            'correo'                        => $datos['correo'] ?? $actual->correo,
            'telefono'                      => $datos['telefono'] ?? $actual->telefono,
            'url_cvlac'                     => $datos['url_cvlac'] ?? $actual->urlCvlac,
            'fecha_actualizacion'           => $datos['fecha_actualizacion'] ?? $actual->fechaActualizacion,
            'escalafon'                     => $datos['escalafon'] ?? $actual->escalafon,
            'perfil'                        => $datos['perfil'] ?? $actual->perfil,
            'cat_minciencia'                => array_key_exists('cat_minciencia', $datos) ? $datos['cat_minciencia'] : $actual->catMinciencia,
            'conv_minciencia'               => $datos['conv_minciencia'] ?? $actual->convMinciencia,
            'nacionalidaad'                 => $datos['nacionalidaad'] ?? $actual->nacionalidaad,
            'linea_investigacion_principal' => array_key_exists('linea_investigacion_principal', $datos)
                ? $datos['linea_investigacion_principal']
                : $actual->lineaInvestigacionPrincipal
        ];

        return $this->reemplazar($cedula, $datosCompletos);
    }

    public function retirar(int $cedula): bool
    {
        // 1. Verificar si existe
        $this->obtenerPorCedula($cedula, incluirEstudios: false);

        // 2. Regla de Negocio Estricta: No se puede retirar el docente si tiene estudios registrados
        $estudiosActivos = $this->repoEstudios->contarPorDocente($cedula);
        if ($estudiosActivos > 0) {
            throw new ConflictoIntegridadExcepcion(
                'No se puede retirar el docente porque tiene estudios registrados.',
                409
            );
        }

        // 3. Ejecutar el procedimiento de retiro (que también tiene el SIGNAL de respaldo)
        return $this->repoDocente->retirar($cedula);
    }
}
