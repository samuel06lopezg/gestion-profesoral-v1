<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioUsuario;
use Throwable;

class ControladorUsuario
{
    public function __construct(
        private readonly IServicioUsuario $servicio
    ) {
    }

    public function listar(): void
    {
        try {
            $usuarios = $this->servicio->obtenerTodos();
            $datos = array_map(fn($u) => $u->toArray(), $usuarios);

            $this->responderJson(200, [
                'tabla' => 'usuario',
                'total' => count($datos),
                'datos' => $datos,
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function obtenerPorId(int $id): void
    {
        try {
            $usuario = $this->servicio->obtenerPorId($id);
            $this->responderJson(200, $usuario->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, ['estado' => 404, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function crear(array $body): void
    {
        try {
            $usuario = $this->servicio->crear($body);
            $this->responderJson(201, [
                'estado'  => 201,
                'mensaje' => 'Usuario creado correctamente.',
                'datos'   => $usuario->toArray(),
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, ['estado' => 409, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function actualizar(int $id, array $body): void
    {
        try {
            $usuario = $this->servicio->actualizar($id, $body);
            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Usuario actualizado correctamente.',
                'datos'   => $usuario->toArray(),
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, ['estado' => 404, 'mensaje' => $e->getMessage()]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, ['estado' => 409, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function eliminar(int $id): void
    {
        try {
            $this->servicio->eliminar($id);
            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Usuario retirado correctamente.',
                'id'      => $id,
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, ['estado' => 404, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    private function responderJson(int $estado, array $cuerpo): void
    {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    }
}
