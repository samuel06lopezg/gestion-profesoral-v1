<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\ConflictoIntegridadExcepcion;
use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioDocente;
use InvalidArgumentException;
use Throwable;

class ControladorDocente
{
    public function __construct(private IServicioDocente $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $docentes = $this->servicio->obtenerTodos($limite);

            if (empty($docentes)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($d) => $d->toArray(), $docentes);

            $this->responderJson(200, [
                'tabla'  => 'docente',
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

    public function obtenerUno(int $cedula): void
    {
        try {
            $docente = $this->servicio->obtenerPorCedula($cedula, incluirEstudios: true);
            $this->responderJson(200, $docente->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Docente no encontrado.',
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
            $errores = $this->validarCamposObligatorios($cuerpo, incluirCedula: true);
            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $docente = $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Docente creado exitosamente.',
                'cedula'  => $docente->cedula
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

    public function reemplazar(int $cedula, array $cuerpo): void
    {
        try {
            $errores = $this->validarCamposObligatorios($cuerpo, incluirCedula: false);
            if (!empty($errores)) {
                $this->responderJson(422, [
                    'estado'  => 422,
                    'mensaje' => 'Datos inválidos.',
                    'errores' => $errores
                ]);
                return;
            }

            $this->servicio->reemplazar($cedula, $cuerpo);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Docente actualizado exitosamente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Docente no encontrado.',
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

    public function actualizarParcial(int $cedula, array $cuerpo): void
    {
        try {
            if (empty($cuerpo)) {
                $this->responderJson(400, [
                    'estado'  => 400,
                    'mensaje' => 'Petición inválida: el cuerpo no puede estar vacío.'
                ]);
                return;
            }

            $this->servicio->actualizarParcial($cedula, $cuerpo);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Docente actualizado parcialmente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Docente no encontrado.',
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

    public function eliminar(int $cedula): void
    {
        try {
            $this->servicio->retirar($cedula);

            $this->responderJson(200, [
                'estado'         => 200,
                'mensaje'        => 'Docente retirado exitosamente.',
                'filasAfectadas' => 1
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->responderJson(404, [
                'estado'  => 404,
                'mensaje' => 'Docente no encontrado.',
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

    private function validarCamposObligatorios(array $cuerpo, bool $incluirCedula): array
    {
        $campos = [
            'nombres', 'apellidos', 'genero', 'cargo', 'fecha_nacimiento',
            'correo', 'telefono', 'url_cvlac', 'fecha_actualizacion', 'escalafon',
            'perfil', 'conv_minciencia', 'nacionalidaad'
        ];

        if ($incluirCedula) {
            array_unshift($campos, 'cedula');
        }

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
