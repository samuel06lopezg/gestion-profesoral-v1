<?php
declare(strict_types=1);
namespace ApiGestion\Servicios;
use ApiGestion\Repositorios\RepositorioTerminoClaveMariaDB;

class ServicioTerminoClave {
    public function __construct(private RepositorioTerminoClaveMariaDB $repo) {}
    public function obtenerTodos(int $limite = 1000): array {
        return $this->repo->obtenerTodos($limite);
    }
}
