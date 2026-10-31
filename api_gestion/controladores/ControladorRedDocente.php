<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioRedDocente;
use InvalidArgumentException;
use Throwable;

class ControladorRedDocente
{
    public function __construct(private IServicioRedDocente $servicio) {}

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
                'tabla'  => 'red_docente',
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

    public function obtenerUno(int $red, int $docente): void
    {
        try {
            $item = $this->servicio->obtenerPorClaves($red, $docente);
            $this->responderJson(200, $item->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Vinculación a red no encontrada.',
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
                'mensaje' => 'Vinculación a red académica creada exitosamente.'
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

    public function reemplazar(int $red, int $docente, array $cuerpo): void
    {
        try {
            $errores = [];
            if (!isset($cuerpo['fecha_inicio']) || $cuerpo['fecha_inicio'] === '') {
                $errores[] = 'El campo fecha_inicio es obligatorio.';
            }

            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $afectadas = $this->servicio->reemplazar($red, $docente, $cuerpo) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Vinculación a red actualizada.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Vinculación no encontrada.',
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

    public function actualizarParcial(int $red, int $docente, array $cuerpo): void
    {
        try {
            $afectadas = $this->servicio->actualizarParcial($red, $docente, $cuerpo) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Vinculación a red actualizada parcialmente.',
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
                'mensaje' => 'Vinculación no encontrada.',
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

    public function eliminar(int $red, int $docente): void
    {
        try {
            $afectadas = $this->servicio->retirar($red, $docente) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Vinculación a red retirada exitosamente.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Vinculación no encontrada.',
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
        $campos = ['red', 'docente', 'fecha_inicio'];
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
