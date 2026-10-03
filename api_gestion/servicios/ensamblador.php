<?php
declare(strict_types=1);

use ApiGestion\Repositorios\RepositorioProgramaMariaDB;
use ApiGestion\Repositorios\RepositorioAreaConocimientoMariaDB;
use ApiGestion\Repositorios\RepositorioTerminoClaveMariaDB;
use ApiGestion\Repositorios\RepositorioLineaInvestigacionMariaDB;
use ApiGestion\Repositorios\RepositorioRedMariaDB;

use ApiGestion\Servicios\ServicioPrograma;
use ApiGestion\Servicios\ServicioAreaConocimiento;
use ApiGestion\Servicios\ServicioTerminoClave;
use ApiGestion\Servicios\ServicioLineaInvestigacion;
use ApiGestion\Servicios\ServicioRed;

function obtenerServicioPrograma(PDO $conexion): ServicioPrograma {
    return new ServicioPrograma(new RepositorioProgramaMariaDB($conexion));
}

function obtenerServicioAreaConocimiento(PDO $conexion): ServicioAreaConocimiento {
    return new ServicioAreaConocimiento(new RepositorioAreaConocimientoMariaDB($conexion));
}

function obtenerServicioTerminoClave(PDO $conexion): ServicioTerminoClave {
    return new ServicioTerminoClave(new RepositorioTerminoClaveMariaDB($conexion));
}

function obtenerServicioLineaInvestigacion(PDO $conexion): ServicioLineaInvestigacion {
    return new ServicioLineaInvestigacion(new RepositorioLineaInvestigacionMariaDB($conexion));
}

function obtenerServicioRed(PDO $conexion): ServicioRed {
    return new ServicioRed(new RepositorioRedMariaDB($conexion));
}
