<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioRed;
use InvalidArgumentException;
use Throwable;

// El controlador es la capa HTTP: revisa que el pedido venga bien armado,
// llama al servicio y traduce el resultado (o la excepción) a un código HTTP.
// NO escribe SQL y NO aplica reglas de negocio.
class ControladorRed
{
    public function __construct(private IServicioRed $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $redes = $this->servicio->obtenerTodos($limite);

            // Sin filas activas: 204 (sin cuerpo). No es un error.
            if (empty($redes)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($r) => $r->toArray(), $redes);

            $this->responderJson(200, [
                'tabla'  => 'red',
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
            $red = $this->servicio->obtenerPorId($id);
            $this->responderJson(200, $red->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function crear(array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo, incluirId: true);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Red creada exitosamente.'
            ]);
        } catch (Throwable $e) {
            // Aquí cae, por ejemplo, una llave duplicada (la base la rechaza)
            $this->errorInterno($e);
        }
    }

    // PUT: reemplazar. Exige TODOS los campos; si falta uno, 422.
    public function reemplazar(int $id, array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo, incluirId: false);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            $this->servicio->reemplazar($id, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Red reemplazada.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    // PATCH: actualizar parcial. Acepta solo los campos que se quieran cambiar.
    public function actualizarParcial(int $id, array $cuerpo): void
    {
        try {
            $this->servicio->actualizarParcial($id, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Red actualizada parcialmente.'
            ]);
        } catch (InvalidArgumentException $e) {
            // Cuerpo vacío: la forma está bien, la operación no tiene sentido -> 400
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

    // DELETE: retirar (borrado lógico). La fila se queda con activo = FALSE.
    public function eliminar(int $id): void
    {
        try {
            $this->servicio->retirar($id);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Red retirada exitosamente.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    // Revisa la FORMA del cuerpo: que estén todos los campos y que idr sea un número.
    private function validar(array $cuerpo, bool $incluirId): array
    {
        $campos = ['nombre', 'url', 'pais'];
        if ($incluirId) {
            array_unshift($campos, 'idr');
        }

        $errores = [];
        foreach ($campos as $campo) {
            if (!array_key_exists($campo, $cuerpo) || $cuerpo[$campo] === '' || $cuerpo[$campo] === null) {
                $errores[] = "El campo {$campo} es obligatorio.";
            }
        }

        if ($incluirId && isset($cuerpo['idr']) && !is_numeric($cuerpo['idr'])) {
            $errores[] = 'El campo idr debe ser un número.';
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
            'mensaje' => 'Red no encontrada.',
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