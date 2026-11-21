<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Servicios\IServicioRolUsuario;
use Throwable;

class ControladorRolUsuario
{
    public function __construct(
        private readonly IServicioRolUsuario $servicio
    ) {
    }

    public function listar(): void
    {
        try {
            $asignaciones = $this->servicio->obtenerTodos();
            $datos = array_map(fn($a) => $a->toArray(), $asignaciones);

            $this->responderJson(200, [
                'tabla' => 'rol_usuario',
                'total' => count($datos),
                'datos' => $datos,
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function obtenerRolesPorUsuario(int $usuarioId): void
    {
        try {
            $roles = $this->servicio->obtenerRolesPorUsuario($usuarioId);
            $datos = array_map(fn($r) => $r->toArray(), $roles);

            $this->responderJson(200, [
                'usuario_id' => $usuarioId,
                'total'      => count($datos),
                'roles'      => $datos,
            ]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function asignar(array $body): void
    {
        $usuarioId = isset($body['usuario_id']) ? (int) $body['usuario_id'] : 0;
        $rolId = isset($body['rol_id']) ? (int) $body['rol_id'] : 0;

        if ($usuarioId <= 0 || $rolId <= 0) {
            $this->responderJson(422, [
                'estado'  => 422,
                'mensaje' => 'Datos de entrada invalidos.',
                'errores' => ['Los campos usuario_id y rol_id son obligatorios y deben ser enteros positivos.'],
            ]);
            return;
        }

        try {
            $this->servicio->asignarRol($usuarioId, $rolId);
            $this->responderJson(201, [
                'estado'  => 201,
                'mensaje' => 'Rol asignado correctamente al usuario.',
                'datos'   => [
                    'usuario_id' => $usuarioId,
                    'rol_id'     => $rolId,
                ],
            ]);
        } catch (ConflictoIntegridadExcepcion $e) {
            $this->responderJson(409, ['estado' => 409, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->responderJson(500, ['estado' => 500, 'mensaje' => 'Error interno.', 'detalle' => $e->getMessage()]);
        }
    }

    public function desasignar(array $body): void
    {
        $usuarioId = isset($body['usuario_id']) ? (int) $body['usuario_id'] : 0;
        $rolId = isset($body['rol_id']) ? (int) $body['rol_id'] : 0;

        if ($usuarioId <= 0 || $rolId <= 0) {
            $this->responderJson(422, [
                'estado'  => 422,
                'mensaje' => 'Datos de entrada invalidos.',
                'errores' => ['Los campos usuario_id y rol_id son obligatorios.'],
            ]);
            return;
        }

        try {
            $this->servicio->desasignarRol($usuarioId, $rolId);
            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Rol desvinculado correctamente del usuario.',
            ]);
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
