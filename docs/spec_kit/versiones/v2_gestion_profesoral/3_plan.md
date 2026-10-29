# Plan — v2: Maestro-Detalle y Tablas con FK (Gestión Profesoral)

## 1. Arquitectura y Componentes

```
┌────────────────────────┐         ┌────────────────────────┐         ┌────────────────────────┐
│  front_php (puerto 8112)│  HTTP   │  api_gestion (8113)    │   PDO   │  mariadb (puerto 13331)│
│  Plantillas PHP Server │ ──────► │  Controladores         │ ──────► │  SP + Tablas           │
│  Desplegables dinámicos│         │  Servicios de Dominio  │         │  Regla SIGNAL 45000    │
│  Formulario M-Detalle  │         │  Repositorios SP/SQL   │         │  Tablas v1 + v2        │
└────────────────────────┘         └────────────────────────┘         └────────────────────────┘
```

## 2. Organización del Código

```
api_gestion/
├── index.php                      Enrutador principal con rutas v1 y nuevas rutas v2
├── modelos/
│   ├── Docente.php
│   ├── EstudiosRealizados.php
│   ├── EvaluacionDocente.php
│   ├── Experiencia.php
│   └── Reconocimiento.php
├── excepciones/
│   ├── NoEncontradoExcepcion.php
│   └── ConflictoIntegridadExcepcion.php
├── repositorios/
│   ├── errores_de_integridad.php   Traductor de PDOException (1452, 1062, 1451, SQLSTATE 45000) a 409
│   ├── IRepositorioDocente.php
│   ├── RepositorioDocenteMariaDB.php           (Usa Stored Procedures MariaDB con CALL y closeCursor)
│   ├── IRepositorioEstudiosRealizados.php
│   ├── RepositorioEstudiosRealizadosMariaDB.php (Usa Stored Procedures MariaDB con CALL y closeCursor)
│   ├── IRepositorioEvaluacionDocente.php
│   ├── RepositorioEvaluacionDocenteMariaDB.php
│   ├── IRepositorioExperiencia.php
│   ├── RepositorioExperienciaMariaDB.php
│   ├── IRepositorioReconocimiento.php
│   └── RepositorioReconocimientoMariaDB.php
├── servicios/
│   ├── ensamblador.php             Inyector de dependencias (único lugar con new)
│   ├── IServicioDocente.php
│   ├── ServicioDocente.php         (Transacción atómica maestro-detalle + regla retiro con estudios)
│   ├── IServicioEstudiosRealizados.php
│   ├── ServicioEstudiosRealizados.php
│   ├── IServicioEvaluacionDocente.php
│   ├── ServicioEvaluacionDocente.php
│   ├── IServicioExperiencia.php
│   ├── ServicioExperiencia.php
│   ├── IServicioReconocimiento.php
│   └── ServicioReconocimiento.php
└── controladores/
    ├── ControladorDocente.php
    ├── ControladorEstudiosRealizados.php
    ├── ControladorEvaluacionDocente.php
    ├── ControladorExperiencia.php
    └── ControladorReconocimiento.php

front_php/
├── index.php                      Rutas y controladores del Frontend para v2
├── cliente_api.php                Cliente cURL con métodos para docentes, estudios, evaluaciones, etc.
└── vistas/
    ├── plantilla.php              Navbar actualizado con accesos directos
    ├── lista_docentes.php         Listado con botón para ver detalle y retirar
    ├── formulario_docente.php     Formulario Maestro-Detalle con renglones dinámicos de estudios
    ├── lista_estudios.php         Listado de estudios realizados
    ├── formulario_estudio.php     Formulario de estudio con <select> de docentes
    ├── lista_evaluaciones.php     Listado de evaluaciones
    ├── formulario_evaluacion.php  Formulario de evaluación con <select> de docentes
    ├── lista_experiencias.php     Listado de experiencias
    ├── formulario_experiencia.php Formulario de experiencia con <select> de docentes
    ├── lista_reconocimientos.php  Listado de reconocimientos
    └── formulario_reconocimiento.php Formulario de reconocimiento con <select> de docentes

db/
└── init.sql                       Contiene esquemas + ALTER de autoincremento + Procedimientos Almacenados
```

## 3. Decisiones de Diseño

1. **Procedimientos Almacenados en MariaDB:**
   - Se crean mediante `CREATE OR REPLACE PROCEDURE ...` con delimitador `DELIMITER $$`.
   - Cada llamada desde PHP ejecuta `$stmt = $conexion->prepare('CALL sp_...(?, ...)')` y **siempre** llama `$stmt->closeCursor()` para evitar el error MySQL `2014 General error: Cannot execute queries while other unbuffered queries are active`.
   - En MariaDB no existe cláusula `RETURNING`, por lo que las operaciones de creación devuelven la fila persistida con un `SELECT * FROM tabla WHERE ...`.

2. **Regla de Borrado Maestro:**
   - La regla `"No se puede retirar el docente porque tiene estudios registrados."` se implementa tanto en el procedimiento `sp_eliminar_docente` (`SIGNAL SQLSTATE '45000'`) como en el `ServicioDocente` antes de proceder al retiro.
   - Si existen estudios con `activo = 1`, la respuesta de la API es estrictamente **HTTP 409 Conflict**.

3. **Envío Único Maestro-Detalle:**
   - El formulario de docente incluye campos de detalle en arreglos paralelos (`detalle_titulo[]`, `detalle_universidad[]`, `detalle_fecha[]`, `detalle_tipo[]`, `detalle_ciudad[]`, `detalle_ins_acreditada[]`, `detalle_metodologia[]`, `detalle_perfil_egresado[]`, `detalle_pais[]`).
   - El frontend los empaqueta en un único objeto JSON que contiene un array `"estudios": [...]`.
   - El servicio abre una transacción (`$conexion->beginTransaction()`), inserta o actualiza el docente, inserta los estudios asociados y realiza `$conexion->commit()`. Si alguno falla, ejecuta `rollBack()`.

4. **Traducción de Errores a HTTP 409:**
   - En `errores_de_integridad.php`, se examina `$e->errorInfo[0]` y `$e->errorInfo[1]`.
   - Códigos capturados: 1452 (FK inexistente), 1062 (clave duplicada), 1451 (FK restrict), y SQLSTATE '45000' (señales de regla de negocio).
