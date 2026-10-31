<?php
declare(strict_types=1);

// Repositorios V1
use ApiGestion\Repositorios\RepositorioProgramaMariaDB;
use ApiGestion\Repositorios\RepositorioAreaConocimientoMariaDB;
use ApiGestion\Repositorios\RepositorioTerminoClaveMariaDB;
use ApiGestion\Repositorios\RepositorioLineaInvestigacionMariaDB;
use ApiGestion\Repositorios\RepositorioRedMariaDB;

// Repositorios V2 (Docente y Dependientes)
use ApiGestion\Repositorios\RepositorioDocenteMariaDB;
use ApiGestion\Repositorios\RepositorioEstudiosRealizadosMariaDB;
use ApiGestion\Repositorios\RepositorioEvaluacionDocenteMariaDB;
use ApiGestion\Repositorios\RepositorioExperienciaMariaDB;
use ApiGestion\Repositorios\RepositorioReconocimientoMariaDB;

// Repositorios V2 (Vinculaciones y Beneficios)
use ApiGestion\Repositorios\RepositorioDocenteDepartamentoMariaDB;
use ApiGestion\Repositorios\RepositorioInteresFuturoMariaDB;
use ApiGestion\Repositorios\RepositorioRedDocenteMariaDB;
use ApiGestion\Repositorios\RepositorioEstudioAreaConocimientoMariaDB;
use ApiGestion\Repositorios\RepositorioApoyoProfesoralMariaDB;
use ApiGestion\Repositorios\RepositorioBecaMariaDB;

// Servicios V1
use ApiGestion\Servicios\ServicioPrograma;
use ApiGestion\Servicios\ServicioAreaConocimiento;
use ApiGestion\Servicios\ServicioTerminoClave;
use ApiGestion\Servicios\ServicioLineaInvestigacion;
use ApiGestion\Servicios\ServicioRed;

// Servicios V2 (Docente y Dependientes)
use ApiGestion\Servicios\ServicioDocente;
use ApiGestion\Servicios\ServicioEstudiosRealizados;
use ApiGestion\Servicios\ServicioEvaluacionDocente;
use ApiGestion\Servicios\ServicioExperiencia;
use ApiGestion\Servicios\ServicioReconocimiento;

// Servicios V2 (Vinculaciones y Beneficios)
use ApiGestion\Servicios\ServicioDocenteDepartamento;
use ApiGestion\Servicios\ServicioInteresFuturo;
use ApiGestion\Servicios\ServicioRedDocente;
use ApiGestion\Servicios\ServicioEstudioAreaConocimiento;
use ApiGestion\Servicios\ServicioApoyoProfesoral;
use ApiGestion\Servicios\ServicioBeca;

// -----------------------------------------------------------------------------
// FACTORÍAS V1
// -----------------------------------------------------------------------------

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

// -----------------------------------------------------------------------------
// FACTORÍAS V2 (MAESTRO-DETALLE DOCENTE Y TABLAS DEPENDIENTES)
// -----------------------------------------------------------------------------

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

// -----------------------------------------------------------------------------
// FACTORÍAS V2 (VINCULACIONES, INTERESES, REDES, ÁREAS, APOYOS Y BECAS)
// -----------------------------------------------------------------------------

function obtenerServicioDocenteDepartamento(PDO $conexion): ServicioDocenteDepartamento {
    return new ServicioDocenteDepartamento(new RepositorioDocenteDepartamentoMariaDB($conexion));
}

function obtenerServicioInteresFuturo(PDO $conexion): ServicioInteresFuturo {
    return new ServicioInteresFuturo(new RepositorioInteresFuturoMariaDB($conexion));
}

function obtenerServicioRedDocente(PDO $conexion): ServicioRedDocente {
    return new ServicioRedDocente(new RepositorioRedDocenteMariaDB($conexion));
}

function obtenerServicioEstudioAreaConocimiento(PDO $conexion): ServicioEstudioAreaConocimiento {
    return new ServicioEstudioAreaConocimiento(new RepositorioEstudioAreaConocimientoMariaDB($conexion));
}

function obtenerServicioApoyoProfesoral(PDO $conexion): ServicioApoyoProfesoral {
    return new ServicioApoyoProfesoral(new RepositorioApoyoProfesoralMariaDB($conexion));
}

function obtenerServicioBeca(PDO $conexion): ServicioBeca {
    return new ServicioBeca(new RepositorioBecaMariaDB($conexion));
}
