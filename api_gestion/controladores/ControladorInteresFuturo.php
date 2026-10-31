<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioInteresFuturo;
use InvalidArgumentException;
use Throwable;

class ControladorInteresFuturo
{
    public function __construct(private IServicioInteresFuturo $servicio) {}

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
                'tabla'  => 'intereses_futuros',
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

    public function obtenerUno(int $docente, string $terminoClave): void
    {
        try {
            $item = $this->servicio->obtenerPorClaves($docente, $terminoClave);
            $this->responderJson(200, $item->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Interés no encontrado.',
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
            $errores = [];
            if (!isset($cuerpo['docente']) || $cuerpo['docente'] === '' || $cuerpo['docente'] === null) {
                $errores[] = 'El campo docente es obligatorio.';
            }
            if (!isset($cuerpo['termino_clave']) || trim((string)$cuerpo['termino_clave']) === '') {
                $errores[] = 'El campo termino_clave es obligatorio.';
            }

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
                'mensaje' => 'Interés de investigación asignado exitosamente.'
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

    public function eliminar(int $docente, string $terminoClave): void
    {
        try {
            $afectadas = $this->servicio->retirar($docente, $terminoClave) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Interés retirado exitosamente.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Interés no encontrado.',
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

    private function responderJson(int $codigo, array $datos): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
