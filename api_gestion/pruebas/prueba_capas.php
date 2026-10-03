<?php
declare(strict_types=1);

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';
require_once __DIR__ . '/../modelos/Programa.php';
require_once __DIR__ . '/../repositorios/IRepositorioPrograma.php';
require_once __DIR__ . '/../servicios/IServicioPrograma.php';
require_once __DIR__ . '/../servicios/ServicioPrograma.php';

use ApiGestion\Excepciones\NoEncontradoExcepcion;
use ApiGestion\Modelos\Programa;
use ApiGestion\Repositorios\IRepositorioPrograma;
use ApiGestion\Servicios\ServicioPrograma;

/**
 * Repositorio Falso (Mock en memoria) para probar la capa de Servicios.
 */
class RepositorioProgramaFalso implements IRepositorioPrograma
{
    private array $almacen = [];

    public function __construct()
    {
        // Sembrar datos de prueba
        $this->almacen[101] = new Programa(
            id: 101,
            nombre: 'Ingeniería de Software de Prueba',
            tipo: 'Pregrado',
            nivel: 'Profesional',
            fechaCreacion: '2026-01-01',
            numeroCohortes: '5',
            cantGraduados: '100',
            fechaActualizacion: '2026-09-01',
            ciudad: 'Medellín',
            facultad: 1
        );
    }

    public function obtenerTodos(int $limite): array
    {
        return array_slice(array_values($this->almacen), 0, $limite);
    }

    public function obtenerPorId(int $id): ?Programa
    {
        return $this->almacen[$id] ?? null;
    }

    public function crear(Programa $programa): bool
    {
        $this->almacen[$programa->id] = $programa;
        return true;
    }

    public function actualizar(Programa $programa): bool
    {
        if (!isset($this->almacen[$programa->id])) {
            return false;
        }
        $this->almacen[$programa->id] = $programa;
        return true;
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        if (!isset($this->almacen[$id])) {
            return false;
        }
        return true;
    }

    public function eliminar(int $id): bool
    {
        if (!isset($this->almacen[$id])) {
            return false;
        }
        unset($this->almacen[$id]);
        return true;
    }
}

// ============================================================================
// EJECUCIÓN DE PRUEBAS DE LA CAPA DE SERVICIO
// ============================================================================

echo "=== INICIANDO PRUEBA DE CAPAS (SERVICIO CON REPOSITORIO EN MEMORIA) ===\n\n";

$repoFalso = new RepositorioProgramaFalso();
$servicio = new ServicioPrograma($repoFalso);
$pruebasPasadas = 0;

// Prueba 1: Obtener programa existente
try {
    $p = $servicio->obtenerPorId(101);
    assert($p->nombre === 'Ingeniería de Software de Prueba');
    echo "[OK] Prueba 1: Obtención exitosa por ID en la capa de servicios.\n";
    $pruebasPasadas++;
} catch (Throwable $e) {
    echo "[FAIL] Prueba 1 falló: " . $e->getMessage() . "\n";
}

// Prueba 2: Límite inválido lanza InvalidArgumentException
try {
    $servicio->obtenerTodos(0);
    echo "[FAIL] Prueba 2: Debió lanzar InvalidArgumentException para límite <= 0.\n";
} catch (InvalidArgumentException $e) {
    echo "[OK] Prueba 2: Validación de límite exitosa (capturó InvalidArgumentException).\n";
    $pruebasPasadas++;
} catch (Throwable $e) {
    echo "[FAIL] Prueba 2 falló con excepción inesperada: " . $e->getMessage() . "\n";
}

// Prueba 3: Consultar registro inexistente lanza NoEncontradoExcepcion (404)
try {
    $servicio->obtenerPorId(999);
    echo "[FAIL] Prueba 3: Debió lanzar NoEncontradoExcepcion para ID inexistente.\n";
} catch (NoEncontradoExcepcion $e) {
    echo "[OK] Prueba 3: Manejo de registro no encontrado exitoso (capturó NoEncontradoExcepcion).\n";
    $pruebasPasadas++;
} catch (Throwable $e) {
    echo "[FAIL] Prueba 3 falló con excepción inesperada: " . $e->getMessage() . "\n";
}

// Prueba 4: Actualización parcial con cuerpo vacío
try {
    $servicio->actualizarParcial(101, []);
    echo "[FAIL] Prueba 4: Debió lanzar InvalidArgumentException para cuerpo de PATCH vacío.\n";
} catch (InvalidArgumentException $e) {
    echo "[OK] Prueba 4: Validación de PATCH vacío exitosa.\n";
    $pruebasPasadas++;
} catch (Throwable $e) {
    echo "[FAIL] Prueba 4 falló: " . $e->getMessage() . "\n";
}

echo "\n====================================================================\n";
echo "RESULTADO FINAL: {$pruebasPasadas}/4 pruebas superadas con éxito.\n";
echo "====================================================================\n";