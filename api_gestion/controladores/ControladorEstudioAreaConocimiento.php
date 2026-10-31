<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioEstudioAreaConocimiento;
use InvalidArgumentException;
use Throwable;

class ControladorEstudioAreaConocimiento
{
    public function __construct(private IServicioEstudioAreaConocimiento $servicio) {}

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
                'tabla'  => 'estudio_ac',
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

    public function obtenerUno(int $estudio, string $areaConocimiento): void
    {
        try {
            $item = $this->servicio->obtenerPorClaves($estudio, $areaConocimiento);
            $this->responderJson(200, $item->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Asociación de estudio y área de conocimiento no encontrada.',
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
            if (!isset($cuerpo['estudio']) || $cuerpo['estudio'] === '' || $cuerpo['estudio'] === null) {
                $errores[] = 'El campo estudio es obligatorio.';
            }
            if (!isset($cuerpo['area_conocimiento']) || trim((string)$cuerpo['area_conocimiento']) === '') {
                $errores[] = 'El campo area_conocimiento es obligatorio.';
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
                'mensaje' => 'Área de conocimiento asignada exitosamente al estudio.'
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

    public function eliminar(int $estudio, string $areaConocimiento): void
    {
        try {
            $afectadas = $this->servicio->retirar($estudio, $areaConocimiento) ? 1 : 0;

            $this->responderJson(200, [
                'estado'        => 200,
                'mensaje'       => 'Asociación retirada exitosamente.',
                'filasAfectadas'=> $afectadas
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Asociación no encontrada.',
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
