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

// Repositorios V3 (Seguridad, Usuarios y Roles)
use ApiGestion\Repositorios\RepositorioUsuarioMariaDB;
use ApiGestion\Repositorios\RepositorioRolMariaDB;
use ApiGestion\Repositorios\RepositorioRolUsuarioMariaDB;

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

// Servicios V3 (Seguridad, Usuarios y Roles)
use ApiGestion\Servicios\ServicioSesion;
use ApiGestion\Servicios\ServicioUsuario;
use ApiGestion\Servicios\ServicioRol;
use ApiGestion\Servicios\ServicioRolUsuario;

// Controladores (se instancian aquí para que `new` quede concentrado en el ensamblador)
use ApiGestion\Controladores\ControladorPrograma;
use ApiGestion\Controladores\ControladorAreaConocimiento;
use ApiGestion\Controladores\ControladorTerminoClave;
use ApiGestion\Controladores\ControladorLineaInvestigacion;
use ApiGestion\Controladores\ControladorRed;
use ApiGestion\Controladores\ControladorDocente;
use ApiGestion\Controladores\ControladorEstudiosRealizados;
use ApiGestion\Controladores\ControladorEvaluacionDocente;
use ApiGestion\Controladores\ControladorExperiencia;
use ApiGestion\Controladores\ControladorReconocimiento;
use ApiGestion\Controladores\ControladorDocenteDepartamento;
use ApiGestion\Controladores\ControladorInteresFuturo;
use ApiGestion\Controladores\ControladorRedDocente;
use ApiGestion\Controladores\ControladorEstudioAreaConocimiento;
use ApiGestion\Controladores\ControladorApoyoProfesoral;
use ApiGestion\Controladores\ControladorBeca;
use ApiGestion\Controladores\ControladorLogin;
use ApiGestion\Controladores\ControladorUsuario;
use ApiGestion\Controladores\ControladorRol;
use ApiGestion\Controladores\ControladorRolUsuario;

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

// -----------------------------------------------------------------------------
// FACTORÍAS V3 (SEGURIDAD, USUARIOS Y ROLES)
// -----------------------------------------------------------------------------

function obtenerServicioSesion(PDO $conexion): ServicioSesion {
    return new ServicioSesion(new RepositorioUsuarioMariaDB($conexion));
}

function obtenerServicioUsuario(PDO $conexion): ServicioUsuario {
    return new ServicioUsuario(new RepositorioUsuarioMariaDB($conexion));
}

function obtenerServicioRol(PDO $conexion): ServicioRol {
    return new ServicioRol(new RepositorioRolMariaDB($conexion));
}

function obtenerServicioRolUsuario(PDO $conexion): ServicioRolUsuario {
    $repoRolUsuario = new RepositorioRolUsuarioMariaDB($conexion);
    $repoUsuario = new RepositorioUsuarioMariaDB($conexion);
    $repoRol = new RepositorioRolMariaDB($conexion);
    return new ServicioRolUsuario($repoRolUsuario, $repoUsuario, $repoRol);
}

// -----------------------------------------------------------------------------
// CONEXIÓN A LA BASE DE DATOS (Artículo 9: credenciales solo desde el entorno)
// -----------------------------------------------------------------------------

function obtenerConexion(): PDO {
    $host = getenv('MYSQL_HOST') ?: 'mariadb';
    $puerto = getenv('MYSQL_PORT') ?: '3306';
    $base = getenv('MYSQL_DATABASE') ?: 'gestion_profesoral';
    $usuario = getenv('MYSQL_USER') ?: 'root';
    $clave = (string) getenv('MYSQL_PASSWORD');

    return new PDO(
        "mysql:host={$host};port={$puerto};dbname={$base};charset=utf8mb4",
        $usuario,
        $clave,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}

// -----------------------------------------------------------------------------
// FACTORÍA DE CONTROLADORES (única que ejecuta `new` sobre controladores)
// -----------------------------------------------------------------------------

/**
 * Construye el controlador del recurso indicado con su servicio ya inyectado.
 * Los nombres de recurso son los segmentos de la URL (/api/{recurso}).
 */
function obtenerControlador(string $recurso, PDO $c): object {
    return match ($recurso) {
        'programa'             => new ControladorPrograma(obtenerServicioPrograma($c)),
        'area-conocimiento'    => new ControladorAreaConocimiento(obtenerServicioAreaConocimiento($c)),
        'termino-clave'        => new ControladorTerminoClave(obtenerServicioTerminoClave($c)),
        'linea-investigacion'  => new ControladorLineaInvestigacion(obtenerServicioLineaInvestigacion($c)),
        'red'                  => new ControladorRed(obtenerServicioRed($c)),
        'docente'              => new ControladorDocente(obtenerServicioDocente($c)),
        'estudios-realizados'  => new ControladorEstudiosRealizados(obtenerServicioEstudiosRealizados($c)),
        'evaluacion-docente'   => new ControladorEvaluacionDocente(obtenerServicioEvaluacionDocente($c)),
        'experiencia'          => new ControladorExperiencia(obtenerServicioExperiencia($c)),
        'reconocimiento'       => new ControladorReconocimiento(obtenerServicioReconocimiento($c)),
        'docente-departamento' => new ControladorDocenteDepartamento(obtenerServicioDocenteDepartamento($c)),
        'intereses-futuros'    => new ControladorInteresFuturo(obtenerServicioInteresFuturo($c)),
        'red-docente'          => new ControladorRedDocente(obtenerServicioRedDocente($c)),
        'estudio-ac'           => new ControladorEstudioAreaConocimiento(obtenerServicioEstudioAreaConocimiento($c)),
        'apoyo-profesoral'     => new ControladorApoyoProfesoral(obtenerServicioApoyoProfesoral($c)),
        'beca'                 => new ControladorBeca(obtenerServicioBeca($c)),
        'login', 'sesion'      => new ControladorLogin(obtenerServicioSesion($c)),
        'usuario'              => new ControladorUsuario(obtenerServicioUsuario($c)),
        'rol'                  => new ControladorRol(obtenerServicioRol($c)),
        'rol-usuario'          => new ControladorRolUsuario(obtenerServicioRolUsuario($c)),
        default                => throw new InvalidArgumentException("Recurso desconocido: {$recurso}"),
    };
}
