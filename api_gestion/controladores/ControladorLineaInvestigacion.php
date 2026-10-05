<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioLineaInvestigacion;
use InvalidArgumentException;
use Throwable;

class ControladorLineaInvestigacion
{
    public function __construct(private IServicioLineaInvestigacion $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $lineas = $this->servicio->obtenerTodos($limite);

            if (empty($lineas)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($l) => $l->toArray(), $lineas);

            $this->responderJson(200, [
                'tabla'  => 'linea_investigacion',
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
            $this->errorInterno($e);
        }
    }

    public function obtenerUno(int $id): void
    {
        try {
            $linea = $this->servicio->obtenerPorId($id);
            $this->responderJson(200, $linea->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function crear(array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            // El servicio devuelve el id que generó la base
            $nuevoId = $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Línea de investigación creada exitosamente.',
                'id'      => $nuevoId
            ]);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function reemplazar(int $id, array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            $this->servicio->reemplazar($id, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Línea de investigación reemplazada.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function actualizarParcial(int $id, array $cuerpo): void
    {
        try {
            $this->servicio->actualizarParcial($id, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Línea de investigación actualizada parcialmente.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->responderJson(400, [
                'estado'  => 400,
                'mensaje' => 'Petición inválida.',
                'detalle' => $e->getMessage()
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function eliminar(int $id): void
    {
        try {
            $this->servicio->retirar($id);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Línea de investigación retirada exitosamente.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    // Revisa la FORMA: campos obligatorios y que no excedan el tamaño de la columna.
    // Aquí NO hay "id": la base lo genera.
    private function validar(array $cuerpo): array
    {
        $errores = [];

        foreach (['nombre', 'descripcion'] as $campo) {
            if (!array_key_exists($campo, $cuerpo) || $cuerpo[$campo] === '' || $cuerpo[$campo] === null) {
                $errores[] = "El campo {$campo} es obligatorio.";
            }
        }

        if (isset($cuerpo['nombre']) && mb_strlen((string)$cuerpo['nombre']) > 45) {
            $errores[] = 'El campo nombre no puede pasar de 45 caracteres.';
        }
        if (isset($cuerpo['descripcion']) && mb_strlen((string)$cuerpo['descripcion']) > 256) {
            $errores[] = 'El campo descripcion no puede pasar de 256 caracteres.';
        }

        return $errores;
    }

    private function datosInvalidos(array $errores): void
    {
        $this->responderJson(422, [
            'estado'  => 422,
            'mensaje' => 'Datos inválidos.',
            'errores' => $errores
        ]);
    }

    private function noEncontrado(NoEncontradoExcepcion $e): void
    {
        $this->responderJson(404, [
            'estado'  => 404,
            'mensaje' => 'Línea de investigación no encontrada.',
            'detalle' => $e->getMessage()
        ]);
    }

    private function errorInterno(Throwable $e): void
    {
        $this->responderJson(500, [
            'estado'  => 500,
            'mensaje' => 'Error interno.',
            'detalle' => $e->getMessage()
        ]);
    }

    private function responderJson(int $codigo, array $datos): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}