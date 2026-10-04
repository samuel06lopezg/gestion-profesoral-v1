<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioTerminoClave;
use InvalidArgumentException;
use Throwable;

class ControladorTerminoClave
{
    public function __construct(private IServicioTerminoClave $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $terminos = $this->servicio->obtenerTodos($limite);

            if (empty($terminos)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($t) => $t->toArray(), $terminos);

            $this->responderJson(200, [
                'tabla'  => 'termino_clave',
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

    public function obtenerUno(string $termino): void
    {
        try {
            $encontrado = $this->servicio->obtenerPorTermino($termino);
            $this->responderJson(200, $encontrado->toArray());
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function crear(array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo, esCreacion: true);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            $this->servicio->crear($cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Término clave creado exitosamente.'
            ]);
        } catch (Throwable $e) {
            // Aquí cae, por ejemplo, un término repetido (la base lo rechaza)
            $this->errorInterno($e);
        }
    }

    // PUT: reemplazar. La ficha completa debe traer termino_ingles (puede ir vacío).
    public function reemplazar(string $termino, array $cuerpo): void
    {
        try {
            $errores = $this->validar($cuerpo, esCreacion: false);
            if (!empty($errores)) {
                $this->datosInvalidos($errores);
                return;
            }

            $this->servicio->reemplazar($termino, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Término clave reemplazado.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function actualizarParcial(string $termino, array $cuerpo): void
    {
        try {
            $this->servicio->actualizarParcial($termino, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Término clave actualizado parcialmente.'
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

    public function eliminar(string $termino): void
    {
        try {
            $this->servicio->retirar($termino);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Término clave retirado exitosamente.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    // Revisa la FORMA del cuerpo.
    private function validar(array $cuerpo, bool $esCreacion): array
    {
        $errores = [];

        if ($esCreacion) {
            if (!array_key_exists('termino', $cuerpo) || trim((string)$cuerpo['termino']) === '') {
                $errores[] = 'El campo termino es obligatorio.';
            } else {
                if (mb_strlen((string)$cuerpo['termino']) > 30) {
                    $errores[] = 'El campo termino no puede pasar de 30 caracteres.';
                }
                // La llave viaja en la dirección: una barra la rompería
                if (str_contains((string)$cuerpo['termino'], '/')) {
                    $errores[] = 'El campo termino no puede contener el símbolo «/».';
                }
            }
        } elseif (!array_key_exists('termino_ingles', $cuerpo)) {
            $errores[] = 'El campo termino_ingles debe venir en la ficha completa (puede ir vacío).';
        }

        if (isset($cuerpo['termino_ingles']) && mb_strlen((string)$cuerpo['termino_ingles']) > 30) {
            $errores[] = 'El campo termino_ingles no puede pasar de 30 caracteres.';
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
            'mensaje' => 'Término clave no encontrado.',
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