<?php
declare(strict_types=1);
namespace ApiGestion\Servicios;
use ApiGestion\Repositorios\RepositorioAreaConocimientoMariaDB;

class ServicioAreaConocimiento {
    public function __construct(private RepositorioAreaConocimientoMariaDB $repo) {}
    public function obtenerTodos(int $limite = 1000): array {
        return $this->repo->obtenerTodos($limite);
    }
}
