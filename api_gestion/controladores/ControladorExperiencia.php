<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioExperiencia;
use InvalidArgumentException;
use Throwable;

class ControladorExperiencia
{
    public function __construct(private IServicioExperiencia $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $experiencias = $this->servicio->obtenerTodos($limite);

            if (empty($experiencias)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($e) => $e->toArray(), $experiencias);

            $this->responderJson(200, [
                'tabla'  => 'experiecia',
                'limite' => $limite,
                'total'  => count($datos),
                'datos'  => $datos
            ]);
        } catch (InvalidArgumentException $e) {
            $this->responderJson(400, [
                'estado'  => 400,
                'mensaje' => 'Parámetros inválidos.',
                'detalle' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function obtenerUno(int $id): void
    {
        try {
            $experiencia = $this->servicio->obtenerPorId($id);
            $this->responderJson(200, $experiencia->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Experiencia no encontrada.',
                'detalle' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function crear(array $cuerpo): void
    {
        try {
            $errores = $this->validarCamposObligatorios($cuerpo);
            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $experiencia = $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Experiencia creada exitosamente.',
                'id'      => $experiencia->id
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function reemplazar(int $id, array $cuerpo): void
    {
        try {
            $errores = $this->validarCamposObligatorios($cuerpo);
            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $this->servicio->reemplazar($id, $cuerpo);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Experiencia actualizada exitosamente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Experiencia no encontrada.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function actualizarParcial(int $id, array $cuerpo): void
    {
        try {
            if (empty($cuerpo)) {
                $this->responderJson(400, [
                    'estado'  => 400,
                    'mensaje' => 'Petición inválida: el cuerpo no puede estar vacío.'
                ]);
                return;
            }

            $this->servicio->actualizarParcial($id, $cuerpo);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Experiencia actualizada parcialmente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Experiencia no encontrada.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        try {
            $this->servicio->retirar($id);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Experiencia retirada exitosamente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Experiencia no encontrada.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno del servidor.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    private function validarCamposObligatorios(array $cuerpo): array
    {
        $campos = ['nombre_cargo', 'institucion', 'tipo', 'fecha_inicio', 'docente'];
        $errores = [];

        foreach ($campos as $campo) {
            if (!array_key_exists($campo, $cuerpo) || $cuerpo[$campo] === '' || $cuerpo[$campo] === null) {
                $errores[] = "El campo {$campo} es obligatorio.";
            }
        }

        return $errores;
    }

    private function responderJson(int $codigo, array $datos): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
