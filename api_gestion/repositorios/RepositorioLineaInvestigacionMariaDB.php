<?php
declare(strict_types=1);
namespace ApiGestion\Repositorios;
use ApiGestion\Modelos\LineaInvestigacion;
use PDO;

class RepositorioLineaInvestigacionMariaDB {
    public function __construct(private PDO $conexion) {}
    public function obtenerTodos(int $limite): array {
        $stmt = $this->conexion->prepare("SELECT id, nombre, descripcion FROM linea_investigacion WHERE activo = TRUE LIMIT :limite");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $res = [];
        while ($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res[] = new LineaInvestigacion((int)$f['id'], $f['nombre'], $f['descripcion']);
        }
        return $res;
    }
}
