<?php
declare(strict_types=1);
namespace ApiGestion\Repositorios;
use ApiGestion\Modelos\TerminoClave;
use PDO;

class RepositorioTerminoClaveMariaDB {
    public function __construct(private PDO $conexion) {}
    public function obtenerTodos(int $limite): array {
        $stmt = $this->conexion->prepare("SELECT termino, termino_ingles FROM termino_clave WHERE activo = TRUE LIMIT :limite");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $res = [];
        while ($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res[] = new TerminoClave($f['termino'], $f['termino_ingles'] ?? null);
        }
        return $res;
    }
}
