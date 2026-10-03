<?php
declare(strict_types=1);
namespace ApiGestion\Servicios;
use ApiGestion\Repositorios\RepositorioLineaInvestigacionMariaDB;

class ServicioLineaInvestigacion {
    public function __construct(private RepositorioLineaInvestigacionMariaDB $repo) {}
    public function obtenerTodos(int $limite = 1000): array {
        return $this->repo->obtenerTodos($limite);
    }
}
