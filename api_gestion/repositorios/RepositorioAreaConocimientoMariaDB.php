<?php
declare(strict_types=1);
namespace ApiGestion\Repositorios;
use ApiGestion\Modelos\AreaConocimiento;
use PDO;

class RepositorioAreaConocimientoMariaDB {
    public function __construct(private PDO $conexion) {}
    public function obtenerTodos(int $limite): array {
        $stmt = $this->conexion->prepare("SELECT id, gran_area, area, disciplina FROM area_conocimiento WHERE activo = TRUE LIMIT :limite");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $res = [];
        while ($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res[] = new AreaConocimiento((int)$f['id'], $f['gran_area'], $f['area'], $f['disciplina']);
        }
        return $res;
    }
}
