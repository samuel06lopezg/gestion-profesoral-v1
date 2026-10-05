<?php
declare(strict_types=1);

namespace ApiGestion\Controladores;

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Servicios\IServicioAreaConocimiento;
use InvalidArgumentException;
use Throwable;

class ControladorAreaConocimiento
{
    public function __construct(private IServicioAreaConocimiento $servicio) {}

    public function listar(array $parametrosConsulta): void
    {
        try {
            $limite = isset($parametrosConsulta['limite']) ? (int)$parametrosConsulta['limite'] : 1000;
            $areas = $this->servicio->obtenerTodos($limite);

            if (empty($areas)) {
                http_response_code(204);
                return;
            }

            $datos = array_map(fn($a) => $a->toArray(), $areas);

            $this->responderJson(200, [
                'tabla'  => 'area_conocimiento',
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

    public function obtenerUno(string $id): void
    {
        try {
            $area = $this->servicio->obtenerPorId($id);
            $this->responderJson(200, $area->toArray());
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
                'mensaje' => 'Área de conocimiento creada exitosamente.'
            ]);
        } catch (Throwable $e) {
            // Aquí cae, por ejemplo, un código repetido (la base lo rechaza)
            $this->errorInterno($e);
        }
    }

    // PUT: reemplazar. Exige TODOS los campos; si falta uno, 422.
    public function reemplazar(string $id, array $cuerpo): void
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
                'mensaje' => 'Área de conocimiento reemplazada.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    public function actualizarParcial(string $id, array $cuerpo): void
    {
        try {
            $this->servicio->actualizarParcial($id, $cuerpo);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Área de conocimiento actualizada parcialmente.'
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

    public function eliminar(string $id): void
    {
        try {
            $this->servicio->retirar($id);

            $this->responderJson(200, [
                'estado'  => 200,
                'mensaje' => 'Área de conocimiento retirada exitosamente.'
            ]);
        } catch (NoEncontradoExcepcion $e) {
            $this->noEncontrado($e);
        } catch (Throwable $e) {
            $this->errorInterno($e);
        }
    }

    // Revisa la FORMA del cuerpo: campos obligatorios y tamaños máximos.
    private function validar(array $cuerpo, bool $incluirId): array
    {
        $limites = ['gran_area' => 60, 'area' => 60, 'disciplina' => 150];
        if ($incluirId) {
            $limites = ['id' => 6] + $limites;
        }

        $errores = [];
        foreach ($limites as $campo => $maximo) {
            if (!array_key_exists($campo, $cuerpo) || trim((string)$cuerpo[$campo]) === '') {
                $errores[] = "El campo {$campo} es obligatorio.";
            } elseif (mb_strlen((string)$cuerpo[$campo]) > $maximo) {
                $errores[] = "El campo {$campo} no puede pasar de {$maximo} caracteres.";
            }
        }

        // La llave viaja en la dirección: una barra la rompería
        if ($incluirId && isset($cuerpo['id']) && str_contains((string)$cuerpo['id'], '/')) {
            $errores[] = 'El campo id no puede contener el símbolo «/».';
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
            'mensaje' => 'Área de conocimiento no encontrada.',
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