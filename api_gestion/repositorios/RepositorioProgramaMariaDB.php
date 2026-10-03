<?php
declare(strict_types=1);

namespace ApiGestion\Repositorios;

use ApiGestion\Modelos\Programa;
use PDO;

class RepositorioProgramaMariaDB implements IRepositorioPrograma
{
    public function __construct(private PDO $conexion) {}

    public function obtenerTodos(int $limite): array
    {
        $sql = "SELECT id, nombre, tipo, nivel, fecha_creacion, numero_cohortes, 
                       cant_graduados, fecha_actualizacion, ciudad, facultad, fecha_cierre 
                FROM programa 
                WHERE activo = TRUE 
                LIMIT :limite";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $programas = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programas[] = $this->mapearPrograma($fila);
        }

        return $programas;
    }

    public function obtenerPorId(int $id): ?Programa
    {
        $sql = "SELECT id, nombre, tipo, nivel, fecha_creacion, numero_cohortes, 
                       cant_graduados, fecha_actualizacion, ciudad, facultad, fecha_cierre 
                FROM programa 
                WHERE id = :id AND activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearPrograma($fila) : null;
    }

    public function crear(Programa $p): bool
    {
        $sql = "INSERT INTO programa 
                (id, nombre, tipo, nivel, fecha_creacion, numero_cohortes, cant_graduados, fecha_actualizacion, ciudad, facultad, fecha_cierre, activo) 
                VALUES (:id, :nombre, :tipo, :nivel, :fecha_creacion, :numero_cohortes, :cant_graduados, :fecha_actualizacion, :ciudad, :facultad, :fecha_cierre, TRUE)";

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':id'                  => $p->id,
            ':nombre'              => $p->nombre,
            ':tipo'                => $p->tipo,
            ':nivel'               => $p->nivel,
            ':fecha_creacion'      => $p->fechaCreacion,
            ':numero_cohortes'     => $p->numeroCohortes,
            ':cant_graduados'      => $p->cantGraduados,
            ':fecha_actualizacion' => $p->fechaActualizacion,
            ':ciudad'              => $p->ciudad,
            ':facultad'            => $p->facultad,
            ':fecha_cierre'        => $p->fechaCierre
        ]);
    }

    public function actualizar(Programa $p): bool
    {
        $sql = "UPDATE programa SET 
                nombre = :nombre, tipo = :tipo, nivel = :nivel, 
                fecha_creacion = :fecha_creacion, numero_cohortes = :numero_cohortes, 
                cant_graduados = :cant_graduados, fecha_actualizacion = :fecha_actualizacion, 
                ciudad = :ciudad, facultad = :facultad, fecha_cierre = :fecha_cierre 
                WHERE id = :id AND activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id'                  => $p->id,
            ':nombre'              => $p->nombre,
            ':tipo'                => $p->tipo,
            ':nivel'               => $p->nivel,
            ':fecha_creacion'      => $p->fechaCreacion,
            ':numero_cohortes'     => $p->numeroCohortes,
            ':cant_graduados'      => $p->cantGraduados,
            ':fecha_actualizacion' => $p->fechaActualizacion,
            ':ciudad'              => $p->ciudad,
            ':facultad'            => $p->facultad,
            ':fecha_cierre'        => $p->fechaCierre
        ]);

        return $stmt->rowCount() > 0;
    }

    public function actualizarParcial(int $id, array $datos): bool
    {
        if (empty($datos)) {
            return false;
        }

        $campos = [];
        $parametros = [':id' => $id];

        foreach ($datos as $columna => $valor) {
            $campos[] = "{$columna} = :{$columna}";
            $parametros[":{$columna}"] = $valor;
        }

        $sql = "UPDATE programa SET " . implode(', ', $campos) . " WHERE id = :id AND activo = TRUE";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        // Borrado lógico filtrando por activo = TRUE
        $sql = "UPDATE programa SET activo = FALSE WHERE id = :id AND activo = TRUE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function mapearPrograma(array $fila): Programa
    {
        return new Programa(
            id: (int)$fila['id'],
            nombre: $fila['nombre'],
            tipo: $fila['tipo'],
            nivel: $fila['nivel'],
            fechaCreacion: $fila['fecha_creacion'],
            numeroCohortes: $fila['numero_cohortes'],
            cantGraduados: $fila['cant_graduados'],
            fechaActualizacion: $fila['fecha_actualizacion'],
            ciudad: $fila['ciudad'],
            facultad: (int)$fila['facultad'],
            fechaCierre: $fila['fecha_cierre'] ?? null
        );
    }
}