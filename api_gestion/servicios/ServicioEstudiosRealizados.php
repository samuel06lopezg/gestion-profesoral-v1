<?php
declare(strict_types=1);

namespace ApiGestion\Servicios;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\EstudiosRealizados;
use ApiGestion\Repositorios\IRepositorioEstudiosRealizados;
use InvalidArgumentException;

class ServicioEstudiosRealizados implements IServicioEstudiosRealizados
{
    public function __construct(private IRepositorioEstudiosRealizados $repositorio) {}

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {
            throw new InvalidArgumentException('El límite debe ser un entero mayor que cero.');
        }

        return $this->repositorio->obtenerTodos($limite);
    }

    public function obtenerPorId(int $id): EstudiosRealizados
    {
        $estudio = $this->repositorio->obtenerPorId($id);
        if (!$estudio) {
            throw new NoEncontradoExcepcion("No existe el estudio realizado con id {$id}.");
        }

        return $estudio;
    }

    public function obtenerPorDocente(int $cedulaDocente): array
    {
        return $this->repositorio->obtenerPorDocente($cedulaDocente);
    }

    public function crear(array $datos): EstudiosRealizados
    {
        return $this->repositorio->crear($datos);
    }

    public function reemplazar(int $id, array $datos): EstudiosRealizados
    {
        $this->obtenerPorId($id);

        $estudio = $this->repositorio->actualizar($id, $datos);
        if (!$estudio) {
            throw new NoEncontradoExcepcion("No se pudo actualizar el estudio con id {$id}.");
        }

        return $estudio;
    }

    public function actualizarParcial(int $id, array $datos): EstudiosRealizados
    {
        $actual = $this->obtenerPorId($id);

        $datosCompletos = [
            'titulo'          => $datos['titulo'] ?? $actual->titulo,
            'universidad'     => $datos['universidad'] ?? $actual->universidad,
            'fecha'           => $datos['fecha'] ?? $actual->fecha,
            'tipo'            => $datos['tipo'] ?? $actual->tipo,
            'ciudad'          => $datos['ciudad'] ?? $actual->ciudad,
            'docente'         => $datos['docente'] ?? $actual->docente,
            'ins_acreditada'  => $datos['ins_acreditada'] ?? $actual->insAcreditada,
            'metodologia'     => $datos['metodologia'] ?? $actual->metodologia,
            'perfil_egresado' => array_key_exists('perfil_egresado', $datos) ? $datos['perfil_egresado'] : $actual->perfilEgresado,
            'pais'            => $datos['pais'] ?? $actual->pais
        ];

        return $this->reemplazar($id, $datosCompletos);
    }

    public function retirar(int $id): bool
    {
        $this->obtenerPorId($id);
        return $this->repositorio->retirar($id);
    }
}
