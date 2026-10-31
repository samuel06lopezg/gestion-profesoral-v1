<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioApoyoProfesoral;
use InvalidArgumentException;
use Throwable;

class ControladorApoyoProfesoral
{
    public function __construct(private IServicioApoyoProfesoral $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $items = $this->servicio->obtenerTodos($limite);

            if (empty($items)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($item) => $item->toArray(), $items);

            $this->responderJson(200, [
                'tabla'  => 'apoyo_profesoral',
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

    public function obtenerUno(int $estudios): void
    {
        try {
            $item = $this->servicio->obtenerPorId($estudios);
            $this->responderJson(200, $item->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Apoyo profesoral no encontrado.',
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

            $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Apoyo profesoral registrado exitosamente.'
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage(),
                'detalle' => $e->getPrevious()?->getMessage() ?? ''
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function reemplazar(int $estudios, array $cuerpo): void
    {
        try {
            $errores = [];
            $campos = ['con_apoyo', 'institucion', 'tipo'];
            foreach ($campos as $campo) {
                if (!array_key_exists($campo, $cuerpo) || $cuerpo[$campo] === '' || $cuerpo[$campo] === null) {
                    $errores[] = "El campo {$campo} es obligatorio.";
                }
            }

            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $afectadas = $this->servicio->reemplazar($estudios, $cuerpo) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Apoyo profesoral modificado.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Apoyo profesoral no encontrado.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage(),
                'detalle' => $e->getPrevious()?->getMessage() ?? ''
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function actualizarParcial(int $estudios, array $cuerpo): void
    {
        try {
            $afectadas = $this->servicio->actualizarParcial($estudios, $cuerpo) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Apoyo profesoral actualizado parcialmente.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (InvalidArgumentException $e) {
            $this->responderJson(400, [
                'estado'  => 400,
                'mensaje' => 'Petición inválida.',
                'detalle' => $e->getMessage()
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Apoyo profesoral no encontrado.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage(),
                'detalle' => $e->getPrevious()?->getMessage() ?? ''
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public function eliminar(int $estudios): void
    {
        try {
            $afectadas = $this->servicio->retirar($estudios) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Apoyo profesoral retirado exitosamente.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Apoyo profesoral no encontrado.',
                'detalle' => $e->getMessage()
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, [
                'estado'  => 409,
                'mensaje' => $e->getMessage(),
                'detalle' => $e->getPrevious()?->getMessage() ?? ''
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, [
                'estado'  => 500,
                'mensaje' => 'Error interno.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    private function validarCamposObligatorios(array $cuerpo): array
    {
        $campos = ['estudios', 'con_apoyo', 'institucion', 'tipo'];
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
