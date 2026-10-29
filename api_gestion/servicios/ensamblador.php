<?php
declare(strict_types=1);

use ApiGestion\Repositorios\RepositorioProgramaMariaDB;
use ApiGestion\Repositorios\RepositorioAreaConocimientoMariaDB;
use ApiGestion\Repositorios\RepositorioTerminoClaveMariaDB;
use ApiGestion\Repositorios\RepositorioLineaInvestigacionMariaDB;
use ApiGestion\Repositorios\RepositorioRedMariaDB;
use ApiGestion\Repositorios\RepositorioDocenteMariaDB;
use ApiGestion\Repositorios\RepositorioEstudiosRealizadosMariaDB;
use ApiGestion\Repositorios\RepositorioEvaluacionDocenteMariaDB;
use ApiGestion\Repositorios\RepositorioExperienciaMariaDB;
use ApiGestion\Repositorios\RepositorioReconocimientoMariaDB;

use ApiGestion\Servicios\ServicioPrograma;
use ApiGestion\Servicios\ServicioAreaConocimiento;
use ApiGestion\Servicios\ServicioTerminoClave;
use ApiGestion\Servicios\ServicioLineaInvestigacion;
use ApiGestion\Servicios\ServicioRed;
use ApiGestion\Servicios\ServicioDocente;
use ApiGestion\Servicios\ServicioEstudiosRealizados;
use ApiGestion\Servicios\ServicioEvaluacionDocente;
use ApiGestion\Servicios\ServicioExperiencia;
use ApiGestion\Servicios\ServicioReconocimiento;

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

function obtenerServicioDocente(PDO $conexion): ServicioDocente {
    $repoDocente = new RepositorioDocenteMariaDB($conexion);
    $repoEstudios = new RepositorioEstudiosRealizadosMariaDB($conexion);
    return new ServicioDocente($repoDocente, $repoEstudios);
}

function obtenerServicioEstudiosRealizados(PDO $conexion): ServicioEstudiosRealizados {
    return new ServicioEstudiosRealizados(new RepositorioEstudiosRealizadosMariaDB($conexion));
}

function obtenerServicioEvaluacionDocente(PDO $conexion): ServicioEvaluacionDocente {
    return new ServicioEvaluacionDocente(new RepositorioEvaluacionDocenteMariaDB($conexion));
}

function obtenerServicioExperiencia(PDO $conexion): ServicioExperiencia {
    return new ServicioExperiencia(new RepositorioExperienciaMariaDB($conexion));
}

function obtenerServicioReconocimiento(PDO $conexion): ServicioReconocimiento {
    return new ServicioReconocimiento(new RepositorioReconocimientoMariaDB($conexion));
}
