-- =============================================================================
-- Base de datos: gestion_profesoral — Módulo de Gestión Profesoral (MariaDB)
--
-- Se deriva del script del curso (ProyectosDeAula/db_scripts/mysql/gestion_profesoral.sql).
-- Cambios al esquema dado, numerados (deben coincidir con docs/.../5_data_model.md):
--   [C1] area_conocimiento.id: INT -> VARCHAR(6). Los códigos son '1A01', no enteros.
--        Arrastra a estudio_ac.area_conocimiento, que la referencia: también VARCHAR(6).
--   [C2] area_conocimiento.disciplina: VARCHAR(60) -> VARCHAR(150). Hay valores de 124 caracteres.
--   [C3] programa.nombre: VARCHAR(60) -> VARCHAR(150). El más largo tiene 92 caracteres.
--   [C4] red.url: VARCHAR(45) -> VARCHAR(255). Una dirección web real no cabe en 45.
--   [C5] 'activo BOOLEAN NOT NULL DEFAULT TRUE' en las tablas del módulo: el borrado es LÓGICO.
--   [C6] Se corrige 'Cienias Naturales' -> 'Ciencias Naturales' (error de digitación del Excel).
--   [C7] estudios_realizados.id: INT -> INT AUTO_INCREMENT para soporte de inserción en maestro-detalle.
--   [C8] Procedimientos almacenados para maestro-detalle (docente y estudios_realizados) con regla de integridad en sp_eliminar_docente.
--
-- DATOS: las 218 filas de area_conocimiento y las 191 de programa se tomaron del
-- init.sql del repositorio guía del profesor (proyecto_php_gestion_profesoral1),
-- que las extrajo del Excel Mapa_conocimiento/. No las digitó este equipo.
--
-- MariaDB ejecuta este archivo SOLO en el primer arranque (volumen vacío).
-- Para volver a correrlo: docker compose down -v
-- =============================================================================

-- =============================================================================
-- Base de datos: gestion_profesoral
-- Módulo: Gestión Profesoral
-- Tablas del módulo: 16
-- Tablas de gestión de usuarios: 3
-- Total de tablas: 19
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `gestion_profesoral`;
USE `gestion_profesoral`;

-- =============================================
-- TABLAS DEL MÓDULO: GESTIÓN PROFESORAL
-- =============================================

-- Tabla: area_conocimiento
CREATE TABLE IF NOT EXISTS `area_conocimiento` (
  `id` VARCHAR(6) NOT NULL, -- [C1] antes INT
  `gran_area` VARCHAR(60) NOT NULL,
  `area` VARCHAR(60) NOT NULL,
  `disciplina` VARCHAR(150) NOT NULL, -- [C2] antes VARCHAR(60)
  `activo` BOOLEAN NOT NULL DEFAULT TRUE, -- Campo obligatorio para borrado lógico
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Tabla: termino_clave
CREATE TABLE IF NOT EXISTS `termino_clave` (
  `termino` VARCHAR(30) NOT NULL,
  `termino_ingles` VARCHAR(30),
  `activo` BOOLEAN NOT NULL DEFAULT TRUE, -- Campo obligatorio para borrado lógico
  PRIMARY KEY (`termino`)
) ENGINE=InnoDB;

-- Tabla: linea_investigacion
CREATE TABLE IF NOT EXISTS `linea_investigacion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL,
  `descripcion` VARCHAR(256) NOT NULL,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE, -- Campo obligatorio para borrado lógico
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Tabla: programa
CREATE TABLE IF NOT EXISTS `programa` (
  `id` INT NOT NULL,
  `nombre` VARCHAR(150) NOT NULL, -- [C3] antes VARCHAR(60)
  `tipo` VARCHAR(45) NOT NULL,
  `nivel` VARCHAR(45) NOT NULL,
  `fecha_creacion` VARCHAR(45) NOT NULL,
  `fecha_cierre` VARCHAR(45),
  `numero_cohortes` VARCHAR(45) NOT NULL,
  `cant_graduados` VARCHAR(45) NOT NULL,
  `fecha_actualizacion` VARCHAR(45) NOT NULL,
  `ciudad` VARCHAR(45) NOT NULL,
  `facultad` INT NOT NULL,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE, -- Campo obligatorio para borrado lógico
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Tabla: red
CREATE TABLE IF NOT EXISTS `red` (
  `idr` INT NOT NULL,
  `nombre` VARCHAR(45) NOT NULL,
  `url` VARCHAR(255) NOT NULL, -- [C4] antes VARCHAR(45)
  `pais` VARCHAR(45) NOT NULL,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE, -- Campo obligatorio para borrado lógico
  PRIMARY KEY (`idr`)
) ENGINE=InnoDB;

-- Tabla: docente
CREATE TABLE IF NOT EXISTS `docente` (
    `cedula` INT NOT NULL,
    `nombres` VARCHAR(60) NOT NULL,
    `apellidos` VARCHAR(60) NOT NULL,
    `genero` VARCHAR(12) NOT NULL,
    `cargo` VARCHAR(30) NOT NULL,
    `fecha_nacimiento` DATE NOT NULL,
    `correo` VARCHAR(70) NOT NULL,
    `telefono` VARCHAR(20) NOT NULL,
    `url_cvlac` VARCHAR(128) NOT NULL,
    `fecha_actualizacion` DATE NOT NULL,
    `escalafon` VARCHAR(45) NOT NULL,
    `perfil` LONGTEXT NOT NULL,
    `cat_minciencia` VARCHAR(45),
    `conv_minciencia` VARCHAR(45) NOT NULL,
    `nacionalidaad` VARCHAR(45) NOT NULL,
    `linea_investigacion_principal` INT,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`cedula`),
    FOREIGN KEY (`linea_investigacion_principal`) REFERENCES `linea_investigacion`(`id`)
) ENGINE=InnoDB;

-- Tabla: estudios_realizados
CREATE TABLE IF NOT EXISTS `estudios_realizados` (
    `id` INT NOT NULL AUTO_INCREMENT, -- [C7] AUTO_INCREMENT para inserción fluida en maestro-detalle
    `titulo` VARCHAR(45) NOT NULL,
    `universidad` VARCHAR(50) NOT NULL,
    `fecha` DATE NOT NULL,
    `tipo` VARCHAR(45) NOT NULL,
    `ciudad` VARCHAR(45) NOT NULL,
    `docente` INT NOT NULL,
    `ins_acreditada` TINYINT NOT NULL,
    `metodologia` VARCHAR(45) NOT NULL,
    `perfil_egresado` LONGTEXT NOT NULL,
    `pais` VARCHAR(45) NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`)
) ENGINE=InnoDB;

-- Tabla: docente_departamento
CREATE TABLE IF NOT EXISTS `docente_departamento` (
    `docente` INT NOT NULL,
    `departamento` INT NOT NULL,
    `dedicacion` VARCHAR(15) NOT NULL,
    `modalidad` VARCHAR(45) NOT NULL,
    `fecha_ingreso` DATE NOT NULL,
    `fecha_salida` DATE,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`docente`, `departamento`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`),
    FOREIGN KEY (`departamento`) REFERENCES `programa`(`id`)
) ENGINE=InnoDB;

-- Tabla: intereses_futuros
CREATE TABLE IF NOT EXISTS `intereses_futuros` (
    `docente` INT NOT NULL,
    `termino_clave` VARCHAR(30) NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`docente`, `termino_clave`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`),
    FOREIGN KEY (`termino_clave`) REFERENCES `termino_clave`(`termino`)
) ENGINE=InnoDB;

-- Tabla: evaluacion_docente
CREATE TABLE IF NOT EXISTS `evaluacion_docente` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `calificacion` FLOAT NOT NULL,
    `semestre` VARCHAR(45) NOT NULL,
    `docente` INT NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`)
) ENGINE=InnoDB;

-- Tabla: reconocimiento
CREATE TABLE IF NOT EXISTS `reconocimiento` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tipo` VARCHAR(45) NOT NULL,
    `fecha` DATE NOT NULL,
    `institucion` VARCHAR(45) NOT NULL,
    `nombre` VARCHAR(45) NOT NULL,
    `ambito` VARCHAR(45) NOT NULL,
    `docente` INT NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`)
) ENGINE=InnoDB;

-- Tabla: experiecia
CREATE TABLE IF NOT EXISTS `experiecia` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre_cargo` VARCHAR(45) NOT NULL,
    `institucion` VARCHAR(45) NOT NULL,
    `tipo` VARCHAR(45) NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE,
    `docente` INT NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`)
) ENGINE=InnoDB;

-- Tabla: red_docente
CREATE TABLE IF NOT EXISTS `red_docente` (
    `red` INT NOT NULL,
    `docente` INT NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` VARCHAR(45),
    `act_destacadas` LONGTEXT NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`red`, `docente`),
    FOREIGN KEY (`red`) REFERENCES `red`(`idr`),
    FOREIGN KEY (`docente`) REFERENCES `docente`(`cedula`)
) ENGINE=InnoDB;

-- Tabla: estudio_ac
CREATE TABLE IF NOT EXISTS `estudio_ac` (
    `estudio` INT NOT NULL,
    `area_conocimiento` VARCHAR(6) NOT NULL, -- [C1] arrastrado: referencia a area_conocimiento.id
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`estudio`, `area_conocimiento`),
    FOREIGN KEY (`estudio`) REFERENCES `estudios_realizados`(`id`),
    FOREIGN KEY (`area_conocimiento`) REFERENCES `area_conocimiento`(`id`)
) ENGINE=InnoDB;

-- Tabla: apoyo_profesoral
CREATE TABLE IF NOT EXISTS `apoyo_profesoral` (
    `estudios` INT NOT NULL,
    `con_apoyo` TINYINT NOT NULL,
    `institucion` VARCHAR(45) NOT NULL,
    `tipo` VARCHAR(45) NOT NULL,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`estudios`),
    FOREIGN KEY (`estudios`) REFERENCES `estudios_realizados`(`id`)
) ENGINE=InnoDB;

-- Tabla: beca
CREATE TABLE IF NOT EXISTS `beca` (
    `estudios` INT NOT NULL,
    `tipo` VARCHAR(45) NOT NULL,
    `institucion` VARCHAR(80) NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`estudios`),
    FOREIGN KEY (`estudios`) REFERENCES `estudios_realizados`(`id`)
) ENGINE=InnoDB;


-- =============================================
-- MÓDULO DE GESTIÓN DE USUARIOS
-- =============================================

-- Tabla de roles
CREATE TABLE IF NOT EXISTS `rol` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL UNIQUE,
    `descripcion` TEXT,
    `activo` TINYINT(1) DEFAULT 1,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuario` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `nombre_completo` VARCHAR(200),
    `activo` TINYINT(1) DEFAULT 1,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de relación usuario-rol
CREATE TABLE IF NOT EXISTS `rol_usuario` (
    `usuario_id` INT NOT NULL,
    `rol_id` INT NOT NULL,
    PRIMARY KEY (`usuario_id`, `rol_id`),
    FOREIGN KEY (`usuario_id`) REFERENCES `usuario`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rol_id`) REFERENCES `rol`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Datos semilla para gestión de usuarios (v3)
INSERT INTO `rol` (`id`, `nombre`, `descripcion`, `activo`) VALUES
    (1, 'admin', 'Administrador con acceso total a lectura, escritura y gestion de identidades', 1),
    (2, 'docente', 'Usuario docente con acceso a gestion de informacion academica del modulo', 1),
    (3, 'consulta', 'Usuario de solo lectura; sin permisos de creacion, modificacion ni retiro', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

INSERT INTO `usuario` (`id`, `username`, `password`, `email`, `nombre_completo`, `activo`) VALUES
    (1, 'admin', '$2y$10$mAumWwmY/KsTXrs1.sUEGeZbAnS859dkSea7ExZ8QeSfejnG/D59i', 'admin@universidad.edu.co', 'Administrador del Sistema', 1),
    (2, 'docente1', '$2y$10$8FcofMVD97q93zY7ohDure1xF8j87klejGgA8d6s0aNAoc7Als0lO', 'docente1@universidad.edu.co', 'Profesor Carlos Gómez', 1),
    (3, 'consultor1', '$2y$10$8IV0ytjvNsOGVXcVUxGaUuyZ2q4B7A7Wul7SnDGE61whru.WQLub2', 'consulta@universidad.edu.co', 'Auditor de Consulta', 1)
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

INSERT INTO `rol_usuario` (`usuario_id`, `rol_id`) VALUES
    (1, 1),
    (2, 2),
    (3, 3)
ON DUPLICATE KEY UPDATE `usuario_id` = VALUES(`usuario_id`);


-- DATOS DE REFERENCIA (del Excel del módulo)
-- ============================================================

-- area_conocimiento: 218 filas
INSERT IGNORE INTO area_conocimiento (id, gran_area, area, disciplina) VALUES
    ('1A01', 'Ciencias Naturales', 'Matemáticas', 'Matemáticas puras'),
    ('1A02', 'Ciencias Naturales', 'Matemáticas', 'Matemáticas aplicadas'),
    ('1A03', 'Ciencias Naturales', 'Matemáticas', 'Estadística y probabilidades (investigación en metodologías)'),
    ('1B01', 'Ciencias Naturales', 'Coputación y ciencias de la información', 'Ciencias de la Computación'),
    ('1B02', 'Ciencias Naturales', 'Coputación y ciencias de la información', 'Ciencias de la Información y bioinformática (hardware en 2.B y aspectos sociales en 5.8)'),
    ('1C01', 'Ciencias Naturales', 'Ciencias físicas', 'Física Atómica, molecular y química'),
    ('1C02', 'Ciencias Naturales', 'Ciencias físicas', 'Física de la materia'),
    ('1C03', 'Ciencias Naturales', 'Ciencias físicas', 'Física de partículas y campos'),
    ('1C04', 'Ciencias Naturales', 'Ciencias físicas', 'Física nuclear'),
    ('1C05', 'Ciencias Naturales', 'Ciencias físicas', 'Física de plasmas y fluidos'),
    ('1C06', 'Ciencias Naturales', 'Ciencias físicas', 'Óptica'),
    ('1C07', 'Ciencias Naturales', 'Ciencias físicas', 'Acústica'),
    ('1C08', 'Ciencias Naturales', 'Ciencias físicas', 'Astronomía'),
    ('1D01', 'Ciencias Naturales', 'Ciencias químicas', 'Química orgánica'),
    ('1D02', 'Ciencias Naturales', 'Ciencias químicas', 'Química inorgánica y nuclear'),
    ('1D03', 'Ciencias Naturales', 'Ciencias químicas', 'Química física'),
    ('1D04', 'Ciencias Naturales', 'Ciencias químicas', 'Ciencia de los polímeros'),
    ('1D05', 'Ciencias Naturales', 'Ciencias químicas', 'Electroquímica'),
    ('1D06', 'Ciencias Naturales', 'Ciencias químicas', 'Química de los coloides'),
    ('1D07', 'Ciencias Naturales', 'Ciencias químicas', 'Química analítica'),
    ('1E01', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Geociencias (multidisciplinario)'),
    ('1E02', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Mineralogía'),
    ('1E03', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Paleontología'),
    ('1E04', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Geoquímica y geofísica'),
    ('1E05', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Geografía Física'),
    ('1E06', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Geología'),
    ('1E07', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Vulcanología'),
    ('1E08', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Ciencias del medio ambiente (aspectos sociales en 5.G)'),
    ('1E09', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Meteorología y ciencias atmosféricas'),
    ('1E10', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Investicación del clima.'),
    ('1E11', 'Ciencias Naturales', 'Ciencias de la tierra y medioambientales', 'Oceanografía, hidrología y recursos del agua'),
    ('1F01', 'Ciencias Naturales', 'Ciencias biológicas', 'Biología celular y microbiología'),
    ('1F02', 'Ciencias Naturales', 'Ciencias biológicas', 'Virología'),
    ('1F03', 'Ciencias Naturales', 'Ciencias biológicas', 'Bioquímica y biología molecular'),
    ('1F04', 'Ciencias Naturales', 'Ciencias biológicas', 'Métodos de investigación en bioquímica'),
    ('1F05', 'Ciencias Naturales', 'Ciencias biológicas', 'Micología'),
    ('1F06', 'Ciencias Naturales', 'Ciencias biológicas', 'Biofísica'),
    ('1F07', 'Ciencias Naturales', 'Ciencias biológicas', 'Genética y herencia (aspectos médicos en 3)'),
    ('1F08', 'Ciencias Naturales', 'Ciencias biológicas', 'Biología reproductiva (aspectos médicos en 3)'),
    ('1F09', 'Ciencias Naturales', 'Ciencias biológicas', 'Biología del desarrollo'),
    ('1F10', 'Ciencias Naturales', 'Ciencias biológicas', 'Botánica y ciencias de las plantas'),
    ('1F11', 'Ciencias Naturales', 'Ciencias biológicas', 'Zoología, Ornitología, Entomología, ciencias biológicas del comportamiento'),
    ('1F12', 'Ciencias Naturales', 'Ciencias biológicas', 'Biología marina del agua'),
    ('1F13', 'Ciencias Naturales', 'Ciencias biológicas', 'Ecología'),
    ('1F14', 'Ciencias Naturales', 'Ciencias biológicas', 'Conservación de la biodiversidad'),
    ('1F15', 'Ciencias Naturales', 'Ciencias biológicas', 'Biología (Teórica, matemática, criobiología, evolutiva…)'),
    ('1F16', 'Ciencias Naturales', 'Ciencias biológicas', 'Otras Biologías'),
    ('1G01', 'Ciencias Naturales', 'Otras ciencias naturales', 'Otras ciencias naturales'),
    ('2A01', 'Ingeniería y Tecnología', 'Ingeniería civil', 'Ingeniería civil'),
    ('2A02', 'Ingeniería y Tecnología', 'Ingeniería civil', 'Ingeniería arquitectónica'),
    ('2A03', 'Ingeniería y Tecnología', 'Ingeniería civil', 'Ingeniería de la construcción'),
    ('2A04', 'Ingeniería y Tecnología', 'Ingeniería civil', 'Ingeniería estructural y municipal'),
    ('2A05', 'Ingeniería y Tecnología', 'Ingeniería civil', 'Ingeniería del transporte'),
    ('2B01', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Ingeniería eléctrica y electrónica'),
    ('2B02', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Robótica y control automático'),
    ('2B03', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Automatización y sistemas de control'),
    ('2B04', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Ingeniería de sistemas y comunicaciones'),
    ('2B05', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Telecomunicaciones'),
    ('2B06', 'Ingeniería y Tecnología', 'Ingenierías Eléctrica, Electrónica e Informática', 'Hardware y arquitectura de computadores'),
    ('2C01', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Ingeniería mecánica'),
    ('2C02', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Mecánica aplicada'),
    ('2C03', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Termodinámica'),
    ('2C04', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Ingeniería aeroespacial'),
    ('2C05', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Ingeniería nuclear (física nuclear en 1.C)'),
    ('2C06', 'Ingeniería y Tecnología', 'Ingeniería Mecánica', 'Ingeniería de audio'),
    ('2D01', 'Ingeniería y Tecnología', 'Ingeniería Química', 'Ingeniería química (plantas y productos)'),
    ('2D02', 'Ingeniería y Tecnología', 'Ingeniería Química', 'Ingeniería de procesos'),
    ('2E01', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Ingeniería mecánica'),
    ('2E02', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Cerámicos'),
    ('2E03', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Recubrimientos y películas'),
    ('2E04', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Compuestos (laminados, plásticos reforzados, fira sintéticas y naturales, e ECA.)'),
    ('2E05', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Papel y madera'),
    ('2E06', 'Ingeniería y Tecnología', 'Ingeniería de los Materiales', 'Textiles (Nanomateriales en 2.J y biomateriales en 2.I)'),
    ('2F01', 'Ingeniería y Tecnología', 'Ingeniería Médica', 'Ingeniería médica'),
    ('2F02', 'Ingeniería y Tecnología', 'Ingeniería Médica', 'Tecnología médica de laboratorio (análisis de muestras, tecnologías para el diagnóstico)'),
    ('2G01', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Ingeniería ambiental y geológica'),
    ('2G02', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Geotécnicas'),
    ('2G03', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Ingeniería del petróleo (combustibles, aceites), energía y combustibles'),
    ('2G04', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Sensores remotos'),
    ('2G05', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Mineria y procesamiento de minerales'),
    ('2G06', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Ingeniería marina, naves'),
    ('2G07', 'Ingeniería y Tecnología', 'Ingeniería Ambiental', 'Ingeniería oceanográfica'),
    ('2H01', 'Ingeniería y Tecnología', 'Biotecnología Ambiental', 'Biotecnología industrial'),
    ('2H02', 'Ingeniería y Tecnología', 'Biotecnología Ambiental', 'Bioremediación, biotecnología para el diagnóstico (Chips ADN y biosensores) en manejo ambiental'),
    ('2H03', 'Ingeniería y Tecnología', 'Biotecnología Ambiental', 'Ética relacionada con biotecnología ambiental'),
    ('2I01', 'Ingeniería y Tecnología', 'Biotecnología Industrial', 'Biotecnología industrial'),
    ('2I02', 'Ingeniería y Tecnología', 'Biotecnología Industrial', 'Tecnologías de bioprocesamiento, biocatálisis, fermentación'),
    ('2I03', 'Ingeniería y Tecnología', 'Biotecnología Industrial', 'Bioproductos (productos que se manufacturan usando biotecnología)'),
    ('2J01', 'Ingeniería y Tecnología', 'Nanotecnología', 'Nanomateriales (producción y propiedades)'),
    ('2J02', 'Ingeniería y Tecnología', 'Nanotecnología', 'Nanoprocesos (aplicaciones a nanoescala) (biomateriales en 2.I)'),
    ('2K01', 'Ingeniería y Tecnología', 'Otras Ingenierías y tecnologías', 'Alimentos y bebidas'),
    ('2K02', 'Ingeniería y Tecnología', 'Otras Ingenierías y tecnologías', 'Otras ingenierías y tecnologías'),
    ('2K03', 'Ingeniería y Tecnología', 'Otras Ingenierías y tecnologías', 'Ingeniería de producción'),
    ('2K04', 'Ingeniería y Tecnología', 'Otras Ingenierías y tecnologías', 'Ingeniería Industrial'),
    ('3A01', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Anatomía y morfología (ciencias vegetales en 1.F)'),
    ('3A02', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Genética humana'),
    ('3A03', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Inmunología'),
    ('3A04', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Neurociencias'),
    ('3A05', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Farmacología y farmacia'),
    ('3A06', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Medicina química'),
    ('3A07', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Toxicología'),
    ('3A08', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Fisiología (incluye citología)'),
    ('3A09', 'Ciencias Médicas y de la Salud', 'Medicina básica', 'Patología'),
    ('3B01', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Andrología'),
    ('3B02', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Obstetricia y ginecología'),
    ('3B03', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Pediatría'),
    ('3B04', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Cardiovascular'),
    ('3B05', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Vascular periférico'),
    ('3B06', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Hematología'),
    ('3B07', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Respiratoria'),
    ('3B08', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Cuidado crítico y de emergencia'),
    ('3B09', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Anestesiología'),
    ('3B10', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Ortopédica'),
    ('3B11', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Cirugía'),
    ('3B12', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Radiología, medicina nuclear y de imágenes'),
    ('3B13', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Trasplantes'),
    ('3B14', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Odontología, cirugía oral y medicina oral'),
    ('3B15', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Dermatología y enfermedades venéreas'),
    ('3B16', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Alergias'),
    ('3B17', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Reumatología'),
    ('3B18', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Endocrinología y metabolismo (incluye diabetes y trastornos hormonales)'),
    ('3B19', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Gastroenterología y hepatología'),
    ('3B20', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Urología y nefrología'),
    ('3B21', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Oncología'),
    ('3B22', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Oftalmología'),
    ('3B23', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Otorrinolaringología'),
    ('3B24', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Psiquiatría'),
    ('3B25', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Neurología clínica'),
    ('3B26', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Geriatría'),
    ('3B27', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Medicina general e interna'),
    ('3B28', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Otros temas de medicina clínica'),
    ('3B29', 'Ciencias Médicas y de la Salud', 'Medicina Clínica', 'Medicina complementaria (sistemas alternativos)'),
    ('3C01', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Ciencias del cuidado de la salud y servicios (administración de hospitales y financiamiento)'),
    ('3C02', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Políticas de salud y servicios'),
    ('3C03', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Enfermería'),
    ('3C04', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Nutrición y dietas'),
    ('3C05', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Salud pública'),
    ('3C06', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Medicina tropical'),
    ('3C07', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Parasitología'),
    ('3C08', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'enfermedades infecciosas'),
    ('3C09', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Epidemiología'),
    ('3C10', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Salud ocupacional'),
    ('3C11', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Ciencias del deporte'),
    ('3C12', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Ciencias socio biomédicas (planificación familiar, salud sexual, efectos políticos y sociales de la investigación biomédica)'),
    ('3C13', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Ética'),
    ('3C14', 'Ciencias Médicas y de la Salud', 'Ciencias de la Salud', 'Abuso de sustancias'),
    ('3D01', 'Ciencias Médicas y de la Salud', 'Biotecnología en Salud', 'Biotecnología relacionada con la salud'),
    ('3D02', 'Ciencias Médicas y de la Salud', 'Biotecnología en Salud', 'Tecnologías para la manipulación de células, tejidos, órganos o el organismo (reporducción asistida)'),
    ('3D03', 'Ciencias Médicas y de la Salud', 'Biotecnología en Salud', 'Tecnología para la identificación y funcionamiento del ADN, proteinas y encimas y como influencian la enfermedad'),
    ('3D04', 'Ciencias Médicas y de la Salud', 'Biotecnología en Salud', 'Biomateriales (relacionados con implantes, dispositivos, sensores)'),
    ('3D05', 'Ciencias Médicas y de la Salud', 'Biotecnología en Salud', 'Ética relacionada con la biomedicina.'),
    ('3E01', 'Ciencias Médicas y de la Salud', 'Otras Ciencias Médicas', 'Forénsicas'),
    ('3E02', 'Ciencias Médicas y de la Salud', 'Otras Ciencias Médicas', 'Otras ciencias médicas'),
    ('3E03', 'Ciencias Médicas y de la Salud', 'Otras Ciencias Médicas', 'Fonoaudiología'),
    ('4A01', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Agricultura'),
    ('4A02', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Forestal'),
    ('4A03', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Pesca'),
    ('4A04', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Ciencias del suelo'),
    ('4A05', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Horticultura y viticultura'),
    ('4A06', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Agronomía'),
    ('4A07', 'Ciencias Agrícolas', 'Agricultura, Silvicultura y Pesca', 'Protección y nutrición de las plantas'),
    ('4B01', 'Ciencias Agrícolas', 'Ciencias animales y lechería', 'Ciencias animales y lechería'),
    ('4B02', 'Ciencias Agrícolas', 'Ciencias animales y lechería', 'Crías y mascotas'),
    ('4C01', 'Ciencias Agrícolas', 'Ciencias Veterinarias', 'Ciencias Veterinarias'),
    ('4D01', 'Ciencias Agrícolas', 'Biotecnología Agrícola', 'Biotecnología agrícola y de alimentos'),
    ('4D02', 'Ciencias Agrícolas', 'Biotecnología Agrícola', 'Tecnología MG, clonamiento de ganado, selección asistida, diagnóstico'),
    ('4D03', 'Ciencias Agrícolas', 'Biotecnología Agrícola', 'Ética relacionada a la biotecnología agrícola'),
    ('4E01', 'Ciencias Agrícolas', 'Otras Ciencias Agrícolas', 'Otras ciencias Agrícolas'),
    ('5A01', 'Ciencias Sociales', 'Psicología', 'Psicología (incluye relaciones hombre-máquina)'),
    ('5A02', 'Ciencias Sociales', 'Psicología', 'Psicología (incluye terapias de aprendizaje, habla, visual y otras discapacidades físicas y mentales'),
    ('5B01', 'Ciencias Sociales', 'Economía y Negocios', 'Economía'),
    ('5B02', 'Ciencias Sociales', 'Economía y Negocios', 'Econometría'),
    ('5B03', 'Ciencias Sociales', 'Economía y Negocios', 'Relaciones Industriales'),
    ('5B04', 'Ciencias Sociales', 'Economía y Negocios', 'Negocios y Management'),
    ('5C01', 'Ciencias Sociales', 'Ciencias de la Educación', 'Educación general (incluye capacitación, pedagogía)'),
    ('5C02', 'Ciencias Sociales', 'Ciencias de la Educación', 'Educación especial (para estudios dotados y aquellos con dificultades del aprendizaje)'),
    ('5D01', 'Ciencias Sociales', 'Sociología', 'Sociología'),
    ('5D02', 'Ciencias Sociales', 'Sociología', 'Demografía'),
    ('5D03', 'Ciencias Sociales', 'Sociología', 'Antropología'),
    ('5D04', 'Ciencias Sociales', 'Sociología', 'Etnografía'),
    ('5D05', 'Ciencias Sociales', 'Sociología', 'Temas especiales (Estudios de género, Temas sociales, Estudios de la familia, Trabajo social)'),
    ('5E01', 'Ciencias Sociales', 'Derecho', 'Derecho'),
    ('5E02', 'Ciencias Sociales', 'Derecho', 'Penal'),
    ('5F01', 'Ciencias Sociales', 'Ciencias Políticas', 'Ciencias Políticas'),
    ('5F02', 'Ciencias Sociales', 'Ciencias Políticas', 'Administración Pública'),
    ('5F03', 'Ciencias Sociales', 'Ciencias Políticas', 'teoría organizacional'),
    ('5G01', 'Ciencias Sociales', 'Geografía Social y Económica', 'Ciencias ambientales'),
    ('5G02', 'Ciencias Sociales', 'Geografía Social y Económica', 'Geografía económica y cultural'),
    ('5G03', 'Ciencias Sociales', 'Geografía Social y Económica', 'Estudios urbanos (planificación y desarrollo)'),
    ('5G04', 'Ciencias Sociales', 'Geografía Social y Económica', 'Planificación del transporte y aspectos sociales del transporte'),
    ('5H01', 'Ciencias Sociales', 'Periodismo y Comunicaciones', 'Periodismo'),
    ('5H02', 'Ciencias Sociales', 'Periodismo y Comunicaciones', 'Ciencias de la Información (aspectos sociales)'),
    ('5H03', 'Ciencias Sociales', 'Periodismo y Comunicaciones', 'Bibliotecología'),
    ('5H04', 'Ciencias Sociales', 'Periodismo y Comunicaciones', 'Medios y comunicación social'),
    ('5I01', 'Ciencias Sociales', 'Otras Ciencias Sociales', 'Ciencias Sociales, interdisciplinaria'),
    ('5I02', 'Ciencias Sociales', 'Otras Ciencias Sociales', 'Otras Ciencias Sociales'),
    ('6A01', 'Humanidades', 'Historia y Arqueología', 'Historia (historia de la ciencia y tecnología en 6C)'),
    ('6A02', 'Humanidades', 'Historia y Arqueología', 'Arqueología'),
    ('6A03', 'Humanidades', 'Historia y Arqueología', 'Historia de Colombia'),
    ('6B01', 'Humanidades', 'Idiomas y Literatura', 'Estudios generales del lenguaje'),
    ('6B02', 'Humanidades', 'Idiomas y Literatura', 'Idiomas específicos'),
    ('6B03', 'Humanidades', 'Idiomas y Literatura', 'Estudios literarios'),
    ('6B04', 'Humanidades', 'Idiomas y Literatura', 'Teoría literaria'),
    ('6B05', 'Humanidades', 'Idiomas y Literatura', 'Literatura específica'),
    ('6B06', 'Humanidades', 'Idiomas y Literatura', 'Lingüística'),
    ('6C01', 'Humanidades', 'Otras historias', 'Historia de la Ciencia y la Tecnología'),
    ('6C02', 'Humanidades', 'Otras historias', 'Otras historias especializadas (Se incluye Histora del Arte)'),
    ('6D01', 'Humanidades', 'Arte', 'Artes plásticas y visules'),
    ('6D02', 'Humanidades', 'Arte', 'Música y musicología'),
    ('6D03', 'Humanidades', 'Arte', 'Danza o Artes danzarías'),
    ('6D04', 'Humanidades', 'Arte', 'Teatro, dramaturgia o Artes escénicas'),
    ('6D05', 'Humanidades', 'Arte', 'Otras artes'),
    ('6D06', 'Humanidades', 'Arte', 'Artes audiovisuales'),
    ('6D07', 'Humanidades', 'Arte', 'Arquitectura y urbanismo'),
    ('6D08', 'Humanidades', 'Arte', 'Diseño'),
    ('6E01', 'Humanidades', 'Otras Humanidades', 'Otras humanidades (Se incluye Estudios del folclor)'),
    ('6E02', 'Humanidades', 'Otras Humanidades', 'Filosofía'),
    ('6E03', 'Humanidades', 'Otras Humanidades', 'Teología');

-- programa: 191 filas del Excel del módulo.
--
--   · id, nombre, tipo y facultad vienen TAL CUAL de la hoja `programa`.
--   · nivel se DERIVA del nombre ('Maestría en …' -> 'Maestría'); el
--     `tipo` de la fuente no sirve para eso: dice 'Programa Académico'
--     en las 191 filas.
--   · ciudad se DERIVA siguiendo la cadena del propio Excel:
--     programa.facultad -> facultad.universidad -> universidad.ciudad.
--   · fecha_creacion, numero_cohortes, cant_graduados y
--     fecha_actualizacion quedan en 'sin dato': el Excel NO LAS TRAE, y
--     rellenarlas con números creíbles sería peor que dejar el hueco a
--     la vista. Son NOT NULL, así que un valor hay que poner: se pone
--     uno que se lea como lo que es.
INSERT IGNORE INTO programa (id, nombre, tipo, nivel, fecha_creacion,
                      fecha_cierre, numero_cohortes, cant_graduados,
                      fecha_actualizacion, ciudad, facultad) VALUES
    (10101, 'Administración de Empresas', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 101),
    (10102, 'Contaduría Pública', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 101),
    (10103, 'Economía', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 101),
    (10104, 'Maestría en Dirección de Empresas', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 101),
    (10201, 'Ciencia Política', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 102),
    (10202, 'Derecho', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 102),
    (10203, 'Relaciones Internacionales', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 102),
    (10204, 'Maestría en Derecho y Administración de Justicia', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 102),
    (10301, 'Licenciatura en Filosofía', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10302, 'Licenciatura en Educación para la Primera Infancia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10303, 'Licenciatura en Teología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10304, 'Profesional en Lengua Inglesa', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10305, 'Especialización en Didácticas para Lecturas y Escrituras con Énfasis en Literatura', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10306, 'Especialización en Docencia mediada por las TIC', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10307, 'Especialización en Filosofía Contemporánea', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10308, 'Especialización en Pedagogía y Docencia Universitaria', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10309, 'Maestría en Ciencias de la Educación', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10310, 'Maestría en Didácticas para Lecturas, Escrituras y Literatura', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10311, 'Maestría en Docencia Mediada con las Tic', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10312, 'Maestría en Filosofía Contemporánea', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10313, 'Maestría en Teología de la Biblia', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10314, 'Doctorado en Humanidades Humanismo y Persona', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 103),
    (10401, 'Tecnología en Automatización Industrial', 'Programa Académico', 'Tecnología', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10402, 'Tecnología en Desarrollo de Software', 'Programa Académico', 'Tecnología', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10403, 'Ingeniería Aeronáutica', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10404, 'Ingeniería Electrónica', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10405, 'Ingeniería Mecatrónica', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10406, 'Ingeniería Multimedia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10407, 'Ingeniería de Sistemas', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10408, 'Ingeniería de Sonido', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10409, 'Especialización en Automatización de Procesos Industriales', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10410, 'Especialización en Negocios y Servicios de Telecomunicaciones', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10411, 'Maestría en Ingeniería Aeroespacial', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10412, 'Maestria en Internet de las Cosas y Control', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 104),
    (10501, 'Psicología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (10502, 'Especialización en Evaluación y Diagnóstico Neuropsicológico', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (10503, 'Especialización en Intervención Psicológica en situaciones de Crisis', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (10504, 'Especialización en Psicología de la Seguridad y Salud en el Trabajo', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (10505, 'Maestría en Neuropsicología Clínica', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (10506, 'Maestría en Psicología Clínica', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Bogotá', 105),
    (20101, 'Arquitectura', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20102, 'Ciencias Culinarias de la gastronomía', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20103, 'Diseño de Vestuario', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20104, 'Especialización en Construcción', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20105, 'Maestría en Arquitectura', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20106, 'Maestría en Proyecto Urbano', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 201),
    (20201, 'Administración de Negocios', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20202, 'Contaduría Pública', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20203, 'Economía', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20204, 'Mercadeo y Negocios internacionales', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20205, 'Especialización en Administración de Negocios', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20206, 'Especialización en Administración de la Seguridad', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20207, 'Especialización en Economía Ambiental y Desarrollo Sostenible', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20208, 'Especialización en Finanzas', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20209, 'Especialización en Gerencia Estratégica de Costos', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20210, 'Especialización en Gestión Portuaria y Marítima', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20211, 'Especialización en Mercadeo', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20212, 'Maestría en Dirección Portuaria y Marítima', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20213, 'Maestría en Administración Financiera', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20214, 'Maestría en Gerencia de la Ciencia Tecnología e Innovación', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20215, 'Maestría en Gerencia Turística', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20216, 'Maestría en administración de Negocios', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20217, 'Maestría en administración de Negocios USB Medellín', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20218, 'Maestría en administración de Negocios USB Cali', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20219, 'Doctorado en Administración de Negocios', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 202),
    (20301, 'Licenciatura en Educación Física', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20302, 'Licenciatura en Educación Infantil', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20303, 'Licenciatura en Literatura y Lengua Castellana', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20304, 'Psicología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20305, 'Especialización en Atención Psicosocial a Víctimas y Sobrevivientes', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20306, 'Especialización en Psicología Clínica con orientación Psicoanalítica', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20307, 'Especialización en Psicología de la Salud Ocupacional', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20308, 'Especialización Virtual en gestión de Proyectos Multimediales para Educación', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20309, 'Maestría en Alta Dirección de Servicios Educativos', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20310, 'Maestría en Educación para la Primera Infancia', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20311, 'Maestría en Educación para la Primera Infancia - Pasto', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20312, 'Maestría en Educación: Desarrollo Humano', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20313, 'Maestría en Educación: Desarrollo Humano convenio USB Medellín', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20314, 'Maestría en Psicología', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20315, 'Doctorado en Educación', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20316, 'Doctorado en Psicología', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20317, 'Posdoctorado en Alta Investigación en Educación Intercultural', 'Programa Académico', 'Posdoctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 203),
    (20401, 'Derecho', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20402, 'Gobierno y Relaciones Internacionales', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20403, 'Especialización en Derecho Ambiental', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20404, 'Especialización en Derecho Comercial y de la Empresa', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20405, 'Especialización en Derecho Laboral y de la Seguridad Social', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20406, 'Especialización en Derecho Marítimo y Portuario', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20407, 'Especialización en Derecho Procesal Penal y Criminalística', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20408, 'Especialización Virtual en Derecho Procesal', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20409, 'Especialización Virtual en Desarrollo Territorial y gestión pública', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20410, 'Maestría en Derecho', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20411, 'Doctorado en Derecho', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 204),
    (20501, 'Maestría en Dirección Deportiva y Relaciones Internacionales', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 205),
    (20502, 'Maestría en Preparación Física en Fútbol', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 205),
    (20601, 'Ingeniería Agroindustrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20602, 'Ingeniería Biomédica', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20603, 'Ingeniería Electrónica', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20604, 'Ingeniería Industrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20605, 'Ingeniería Multimedia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20606, 'Ingeniería de Sistemas', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20607, 'Especialización en Gestión Integral de Procesos Productivos y Servicios', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20608, 'Especialización en Gestión Integral de Proyectos', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20609, 'Especialización en Multimedia y experiencia de Usuario', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20610, 'Especialización en Procesos de Desarrollo de Software', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20611, 'Especalización en Redes y Servicios Telemáticos', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20612, 'Maestría en Gerencia de Proyectos', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20613, 'Maestría en Ingeniería: Biotecnología', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20614, 'Maestría en Ingeniería de Software', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (20615, 'Maestría en Tecnologías de la Información para la Analítica de Datos', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cali', 206),
    (30101, 'Arquitectura', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 301),
    (30201, 'Administración del Comercio Internacional', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30202, 'Administración de Negocios', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30203, 'Contaduría Pública', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30204, 'Especialización en Administración de la Seguridad', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30205, 'Especialización en Logística del Comercio Internacional', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30206, 'Maestría en Administración de Negocios', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 302),
    (30301, 'Bacteriología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 303),
    (30302, 'Fisioterapia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 303),
    (30303, 'Fonoaudiología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 303),
    (30304, 'Maestría en Bioquímica Clínica', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 303),
    (30305, 'Maestría en Seguridad y Salud en el Trabajo', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 303),
    (30401, 'Derecho', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 304),
    (30402, 'Gobierno y Relaciones Internacionales', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 304),
    (30403, 'Especialización en Derecho Internacional de los Derechos Humanos y Cultura de la Paz', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 304),
    (30404, 'Especialización en Derecho Laboral y de la Seguridad Social', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 304),
    (30405, 'Especialización en Derecho Marítimo y Portuario', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 304),
    (30501, 'Licenciatura en educación Física, Recreación y Deportes', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30502, 'Licenciatura en Lenguas Modernas con Énfasis en Inglés y Francés', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30503, 'Licenciatura en Educación Infantil', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30504, 'Psicología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30505, 'Especialización en Didácticas para Lecturas y Escrituras con Énfasis en Literatura', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30506, 'Especialización en Pedagogía y Docencia Universitaria', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30507, 'Especialización en Psicología Clínica', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30508, 'Especialización en Psicología de la Educación', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30509, 'Especialización en Teoría y Metodología del Entrenamiento Deportivo', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30510, 'Maestría en Ciencias de la Educación', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30511, 'Maestría en Didáctica del Inglés', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30512, 'Maestría en Psicopatología Clínica y Forense con Énfasis en Intervención con Víctimas', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 305),
    (30601, 'Ingeniería Agroindustrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (30602, 'Ingeniería Industrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (30603, 'Ingeniería Multimedia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (30604, 'Ingeniería Química', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (30605, 'Especialización en Ingeniería de Procesos de Refinación de Petróleos y Petroquímicos Básicos', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (30606, 'Maestría en Ingeniería de Procesos', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Cartagena', 306),
    (40101, 'Arquitectura', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 401),
    (40102, 'Diseño Industrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 401),
    (40103, 'Maestría en Bioclimática', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 401),
    (40104, 'Maestría en Creatividad', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 401),
    (40201, 'Administración de Negocios', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 402),
    (40202, 'Contaduría Pública', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 402),
    (40203, 'Negocios Internacionales', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 402),
    (40204, 'Maestría en Administración de Negocios', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 402),
    (40205, 'Especialización en Gestión Contable Internacional', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 402),
    (40301, 'Derecho', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 403),
    (40302, 'Especialización en Derecho Procesal Constitucional', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 403),
    (40303, 'Especialización en Servicios Públicos Domiciliarios', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 403),
    (40401, 'Licenciaura en Educación Artística', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40402, 'Licenciaura en Educación Física y Deporte', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40403, 'Licenciatura en Educación Infantil', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40404, 'Licenciatura en Educación Infantil Extensión Armenia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40405, 'Tecnología en Entrenamiento Deportivo', 'Programa Académico', 'Tecnología', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40406, 'Licenciatura en Humanidades y Lengua Castellana', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40407, 'Especialización en Dirección y Gestión Educativa', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40408, 'Especialización en Gerencia Educativa - Convenio con la FUCN Virtual', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40409, 'Maestría en Ciencias de la Educación', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40410, 'Maestría en Docencia en Educación Superior en convenio con EAFIT', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40411, 'Maestría en Educación: Desarrollo Humano Armenia', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40412, 'Doctorado en Ciencias de la Educación', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 404),
    (40501, 'Ingeniería Ambiental', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40502, 'Ingeniería de Datos y Software', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40503, 'Ingeniería Industrial', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40504, 'Ingeniería Multimedia', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40505, 'Ingeniería de Sistemas Cibernéticos', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40506, 'Ingeniería de Sonido', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40507, 'Especialización en Gestión de Información y Bases de Datos', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40508, 'Especialización en Posproducción de Audio', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40509, 'Especialización en Seguridad Informática', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40510, 'Especialización en Sistemas de Información Geográfica', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40511, 'Maestría en Geoinformática', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40512, 'Maestría en Ingeniería de Proyectos', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 405),
    (40601, 'Psicología', 'Programa Académico', 'Pregrado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40602, 'Especialización en Medición y Evaluación Psicológica', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40603, 'Especialización en Psicología de los cuidados Paliativos', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40604, 'Especialización en Psicología de las Organizaciones y del Trabajo', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40605, 'Especialización en Psicología de las Organizaciones y del Trabajo - FUCN Virtual', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40606, 'Especialización en Psicología de las Organizaciones y del Trabajo - Confamiliar Risaralda', 'Programa Académico', 'Especialización', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40607, 'Maestría en Neuropsicología', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40608, 'Maestría en Psicología Clinica', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40609, 'Maestría en Psicología de las Organizaciones y del Trabajo', 'Programa Académico', 'Maestría', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406),
    (40610, 'Doctorado en Psicología', 'Programa Académico', 'Doctorado', 'sin dato', NULL, 'sin dato', 'sin dato', 'sin dato', 'Medellín', 406);

-- =============================================================================
-- DATOS SEMILLA V1 Y V2 (Líneas, Términos, Redes, Docentes, Estudios y Vinculaciones)
-- =============================================================================

-- Líneas de investigación
INSERT INTO `linea_investigacion` (`id`, `nombre`, `descripcion`, `activo`) VALUES
    (1, 'Inteligencia Artificial y Robótica', 'Investigación en modelos de aprendizaje profundo, procesamiento de lenguaje natural y robótica autónoma.', 1),
    (2, 'Ingeniería de Software y Sistemas', 'Desarrollo de arquitecturas orientadas a servicios, microservicios, DevOps y calidad del software.', 1),
    (3, 'Ciberseguridad y Redes de Computadores', 'Seguridad perimetral, criptografía aplicada, análisis forense digital y protocolos de red.', 1),
    (4, 'Ciencia de Datos y Analítica Avanzada', 'Minería de datos, Big Data, análisis predictivo y visualización interactiva.', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Términos clave
INSERT INTO `termino_clave` (`termino`, `termino_ingles`, `activo`) VALUES
    ('Machine Learning', 'Machine Learning', 1),
    ('Minería de Datos', 'Data Mining', 1),
    ('Ciberseguridad', 'Cybersecurity', 1),
    ('Computación en la Nube', 'Cloud Computing', 1),
    ('Procesamiento de Lenguaje', 'Natural Language Processing', 1),
    ('Inteligencia Artificial', 'Artificial Intelligence', 1),
    ('Visión Artificial', 'Computer Vision', 1),
    ('Big Data', 'Big Data', 1)
ON DUPLICATE KEY UPDATE `termino` = VALUES(`termino`);

-- Redes académicas
INSERT INTO `red` (`idr`, `nombre`, `url`, `pais`, `activo`) VALUES
    (101, 'Red Colombiana de Computación (Redis)', 'https://redis.org.co', 'Colombia', 1),
    (102, 'Red Iberoamericana de Informática Educativa', 'https://www.ribie.org', 'España', 1),
    (103, 'IEEE Computer Society Colombia', 'https://ieee.org.co', 'Colombia', 1),
    (104, 'ACM Special Interest Group on CS Education', 'https://sigcse.org', 'Estados Unidos', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Docentes
INSERT INTO `docente` (
    `cedula`, `nombres`, `apellidos`, `genero`, `cargo`, `fecha_nacimiento`,
    `correo`, `telefono`, `url_cvlac`, `fecha_actualizacion`, `escalafon`,
    `perfil`, `cat_minciencia`, `conv_minciencia`, `nacionalidaad`,
    `linea_investigacion_principal`, `activo`
) VALUES
    (
        1098765432, 'Carlos Andrés', 'Gómez Restrepo', 'Masculino', 'Profesor Titular', '1980-05-14',
        'carlos.gomez@universidad.edu.co', '3001234567', 'https://scienti.minciencias.gov.co/cvlac/1098765432',
        '2026-03-01', 'Titular', 'Doctor en Ingeniería de Sistemas con énfasis en Inteligencia Artificial y Machine Learning. Con más de 12 años de experiencia docente e investigativa.',
        'Senior', '894', 'Colombiana', 1, 1
    ),
    (
        98765432, 'María Fernanda', 'Morales Ruiz', 'Femenino', 'Profesor Asociado', '1985-11-22',
        'maria.morales@universidad.edu.co', '3109876543', 'https://scienti.minciencias.gov.co/cvlac/98765432',
        '2026-02-15', 'Asociado', 'Magíster en Ingeniería de Software y especialista en Arquitectura de Software y DevOps. Investigadora líder en proyectos de modernización institucional.',
        'Asociado', '894', 'Colombiana', 2, 1
    ),
    (
        1122334455, 'Juan Camilo', 'Valencia Pérez', 'Masculino', 'Profesor Asistente', '1990-08-30',
        'juan.valencia@universidad.edu.co', '3205557788', 'https://scienti.minciencias.gov.co/cvlac/1122334455',
        '2026-01-20', 'Asistente', 'Ingeniero de Sistemas y Magíster en Ciberseguridad. Especialista en seguridad informática perimetral, criptografía y auditoría de sistemas.',
        'Junior', '894', 'Colombiana', 3, 1
    )
ON DUPLICATE KEY UPDATE `nombres` = VALUES(`nombres`);

-- Estudios realizados
INSERT INTO `estudios_realizados` (
    `id`, `titulo`, `universidad`, `fecha`, `tipo`, `ciudad`,
    `docente`, `ins_acreditada`, `metodologia`, `perfil_egresado`, `pais`, `activo`
) VALUES
    (1, 'Ingeniería de Sistemas', 'Universidad de Antioquia', '2003-12-15', 'Pregrado', 'Medellín', 1098765432, 1, 'Presencial', 'Formación sólida en desarrollo, algoritmia y sistemas de información.', 'Colombia', 1),
    (2, 'Maestría en Ciencias de la Computación', 'Universidad Nacional de Colombia', '2008-06-20', 'Maestría', 'Medellín', 1098765432, 1, 'Presencial', 'Investigación en optimización y minería de datos.', 'Colombia', 1),
    (3, 'Doctorado en Informática', 'Universidad Politécnica de Valencia', '2015-11-28', 'Doctorado', 'Valencia', 1098765432, 1, 'Presencial', 'Tesis doctoral en modelos probabilísticos y aprendizaje profundo.', 'España', 1),
    (4, 'Ingeniería de Sistemas e Informática', 'Universidad Pontificia Bolivariana', '2007-06-18', 'Pregrado', 'Medellín', 98765432, 1, 'Presencial', 'Enfoque en arquitectura de software y gestión de proyectos.', 'Colombia', 1),
    (5, 'Maestría en Ingeniería de Software', 'Universidad de los Andes', '2012-10-15', 'Maestría', 'Bogotá', 98765432, 1, 'Presencial', 'Investigación en microservicios y sistemas tolerantes a fallos.', 'Colombia', 1),
    (6, 'Ingeniería de Telecomunicaciones', 'Universidad de San Buenaventura', '2013-12-10', 'Pregrado', 'Medellín', 1122334455, 1, 'Presencial', 'Redes de datos, protocolos y comunicaciones digitales.', 'Colombia', 1),
    (7, 'Maestría en Seguridad de la Información', 'Universidad de Barcelona', '2018-07-22', 'Maestría', 'Barcelona', 1122334455, 1, 'Virtual', 'Ciberseguridad, análisis forense y ethical hacking.', 'España', 1)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);

-- Evaluaciones docentes
INSERT INTO `evaluacion_docente` (`id`, `calificacion`, `semestre`, `docente`, `activo`) VALUES
    (1, 4.8, '2025-1', 1098765432, 1),
    (2, 4.9, '2025-2', 1098765432, 1),
    (3, 4.7, '2025-2', 98765432, 1),
    (4, 4.6, '2025-2', 1122334455, 1)
ON DUPLICATE KEY UPDATE `calificacion` = VALUES(`calificacion`);

-- Reconocimientos
INSERT INTO `reconocimiento` (`id`, `tipo`, `fecha`, `institucion`, `nombre`, `ambito`, `docente`, `activo`) VALUES
    (1, 'Premio a la Excelencia Investigativa', '2024-11-15', 'Ministerio de Ciencia (Minciencias)', 'Medalla al Mérito Científico', 'Nacional', 1098765432, 1),
    (2, 'Docente Destacado del Año', '2023-12-01', 'Universidad de San Buenaventura', 'Mención de Honor Docencia', 'Institucional', 98765432, 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Experiencias
INSERT INTO `experiecia` (`id`, `nombre_cargo`, `institucion`, `tipo`, `fecha_inicio`, `fecha_fin`, `docente`, `activo`) VALUES
    (1, 'Profesor Titular e Investigador', 'Universidad de San Buenaventura', 'Docencia Universitaria', '2016-01-15', NULL, 1098765432, 1),
    (2, 'Líder Técnico de Software', 'Compañía Tecnológica S.A.', 'Profesional', '2012-02-01', '2015-12-31', 98765432, 1),
    (3, 'Consultor en Ciberseguridad', 'Auditorías y Redes Seguras', 'Profesional', '2018-03-01', NULL, 1122334455, 1)
ON DUPLICATE KEY UPDATE `nombre_cargo` = VALUES(`nombre_cargo`);

-- Docente - Departamento / Programa
INSERT INTO `docente_departamento` (`docente`, `departamento`, `dedicacion`, `modalidad`, `fecha_ingreso`, `fecha_salida`, `activo`) VALUES
    (1098765432, 40502, 'Tiempo Completo', 'Presencial', '2016-01-15', NULL, 1),
    (98765432, 40502, 'Tiempo Completo', 'Presencial', '2018-07-01', NULL, 1),
    (1122334455, 40505, 'Medio Tiempo', 'Presencial', '2020-02-01', NULL, 1)
ON DUPLICATE KEY UPDATE `dedicacion` = VALUES(`dedicacion`);

-- Intereses de investigación
INSERT INTO `intereses_futuros` (`docente`, `termino_clave`, `activo`) VALUES
    (1098765432, 'Machine Learning', 1),
    (1098765432, 'Minería de Datos', 1),
    (98765432, 'Computación en la Nube', 1),
    (1122334455, 'Ciberseguridad', 1)
ON DUPLICATE KEY UPDATE `docente` = VALUES(`docente`);

-- Redes - Docente
INSERT INTO `red_docente` (`red`, `docente`, `fecha_inicio`, `fecha_fin`, `act_destacadas`, `activo`) VALUES
    (101, 1098765432, '2018-03-01', 'Vigente', 'Coordinación del comité científico del Congreso Colombiano de Computación.', 1),
    (102, 98765432, '2020-06-15', 'Vigente', 'Publicación de artículos conjuntos y organización de seminarios virtuales.', 1),
    (103, 1122334455, '2021-01-10', 'Vigente', 'Miembro activo del capítulo técnico de seguridad de la información.', 1)
ON DUPLICATE KEY UPDATE `act_destacadas` = VALUES(`act_destacadas`);

-- Estudios - Áreas de conocimiento
INSERT INTO `estudio_ac` (`estudio`, `area_conocimiento`, `activo`) VALUES
    (1, '2B04', 1),
    (2, '1B01', 1),
    (3, '1B01', 1),
    (4, '2B04', 1),
    (5, '2B04', 1),
    (6, '2B05', 1),
    (7, '2B04', 1)
ON DUPLICATE KEY UPDATE `estudio` = VALUES(`estudio`);

-- Apoyos profesorales
INSERT INTO `apoyo_profesoral` (`estudios`, `con_apoyo`, `institucion`, `tipo`, `activo`) VALUES
    (3, 1, 'Universidad de San Buenaventura', 'Comisión de estudios remunerada', 1),
    (5, 1, 'Minciencias', 'Financiación de matrícula y sostenimiento', 1)
ON DUPLICATE KEY UPDATE `institucion` = VALUES(`institucion`);

-- Becas de estudio
INSERT INTO `beca` (`estudios`, `tipo`, `institucion`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
    (3, 'Beca Doctoral Internacional', 'Fundación Carolina / Banco Santander', '2012-09-01', '2015-10-30', 1),
    (7, 'Beca de Excelencia Académica', 'Agencia Española de Cooperación', '2017-09-01', '2018-07-01', 1)
ON DUPLICATE KEY UPDATE `tipo` = VALUES(`tipo`);

-- =============================================================================
-- PROCEDIMIENTOS ALMACENADOS (V2 — Maestro: docente, Detalle: estudios_realizados)
-- =============================================================================

DELIMITER $$

-- 1. Listar docentes activos
CREATE OR REPLACE PROCEDURE sp_listar_docente()
BEGIN
    SELECT * FROM docente WHERE activo = 1 ORDER BY apellidos, nombres;
END$$

-- 2. Consultar docente por cédula
CREATE OR REPLACE PROCEDURE sp_consultar_docente(
    IN p_cedula INT
)
BEGIN
    SELECT * FROM docente WHERE cedula = p_cedula AND activo = 1;
END$$

-- 3. Crear docente
CREATE OR REPLACE PROCEDURE sp_crear_docente(
    IN p_cedula INT,
    IN p_nombres VARCHAR(60),
    IN p_apellidos VARCHAR(60),
    IN p_genero VARCHAR(12),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_correo VARCHAR(70),
    IN p_telefono VARCHAR(20),
    IN p_url_cvlac VARCHAR(128),
    IN p_fecha_actualizacion DATE,
    IN p_escalafon VARCHAR(45),
    IN p_perfil LONGTEXT,
    IN p_cat_minciencia VARCHAR(45),
    IN p_conv_minciencia VARCHAR(45),
    IN p_nacionalidaad VARCHAR(45),
    IN p_linea_investigacion_principal INT
)
BEGIN
    INSERT INTO docente (
        cedula, nombres, apellidos, genero, cargo, fecha_nacimiento,
        correo, telefono, url_cvlac, fecha_actualizacion, escalafon,
        perfil, cat_minciencia, conv_minciencia, nacionalidaad,
        linea_investigacion_principal, activo
    ) VALUES (
        p_cedula, p_nombres, p_apellidos, p_genero, p_cargo, p_fecha_nacimiento,
        p_correo, p_telefono, p_url_cvlac, p_fecha_actualizacion, p_escalafon,
        p_perfil, p_cat_minciencia, p_conv_minciencia, p_nacionalidaad,
        p_linea_investigacion_principal, 1
    );
    SELECT * FROM docente WHERE cedula = p_cedula;
END$$

-- 4. Actualizar docente
CREATE OR REPLACE PROCEDURE sp_actualizar_docente(
    IN p_cedula INT,
    IN p_nombres VARCHAR(60),
    IN p_apellidos VARCHAR(60),
    IN p_genero VARCHAR(12),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_correo VARCHAR(70),
    IN p_telefono VARCHAR(20),
    IN p_url_cvlac VARCHAR(128),
    IN p_fecha_actualizacion DATE,
    IN p_escalafon VARCHAR(45),
    IN p_perfil LONGTEXT,
    IN p_cat_minciencia VARCHAR(45),
    IN p_conv_minciencia VARCHAR(45),
    IN p_nacionalidaad VARCHAR(45),
    IN p_linea_investigacion_principal INT
)
BEGIN
    UPDATE docente SET
        nombres = p_nombres,
        apellidos = p_apellidos,
        genero = p_genero,
        cargo = p_cargo,
        fecha_nacimiento = p_fecha_nacimiento,
        correo = p_correo,
        telefono = p_telefono,
        url_cvlac = p_url_cvlac,
        fecha_actualizacion = p_fecha_actualizacion,
        escalafon = p_escalafon,
        perfil = p_perfil,
        cat_minciencia = p_cat_minciencia,
        conv_minciencia = p_conv_minciencia,
        nacionalidaad = p_nacionalidaad,
        linea_investigacion_principal = p_linea_investigacion_principal
    WHERE cedula = p_cedula AND activo = 1;
    SELECT * FROM docente WHERE cedula = p_cedula;
END$$

-- 5. Eliminar (retirar) docente: REGLA DE NEGOCIO ESTRICTA
-- No se puede retirar el docente si tiene estudios registrados activos
CREATE OR REPLACE PROCEDURE sp_eliminar_docente(
    IN p_cedula INT
)
BEGIN
    DECLARE v_estudios_activos INT DEFAULT 0;

    SELECT COUNT(*) INTO v_estudios_activos
    FROM estudios_realizados
    WHERE docente = p_cedula AND activo = 1;

    IF v_estudios_activos > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede retirar el docente porque tiene estudios registrados.';
    END IF;

    UPDATE docente SET activo = 0 WHERE cedula = p_cedula;
END$$

-- 6. Listar estudios realizados activos
CREATE OR REPLACE PROCEDURE sp_listar_estudios_realizados()
BEGIN
    SELECT * FROM estudios_realizados WHERE activo = 1 ORDER BY fecha DESC, id DESC;
END$$

-- 7. Consultar estudio realizado por ID
CREATE OR REPLACE PROCEDURE sp_consultar_estudio_realizado(
    IN p_id INT
)
BEGIN
    SELECT * FROM estudios_realizados WHERE id = p_id AND activo = 1;
END$$

-- 8. Listar estudios realizados por docente
CREATE OR REPLACE PROCEDURE sp_listar_estudios_por_docente(
    IN p_docente INT
)
BEGIN
    SELECT * FROM estudios_realizados WHERE docente = p_docente AND activo = 1 ORDER BY fecha DESC, id DESC;
END$$

-- 9. Crear estudio realizado
CREATE OR REPLACE PROCEDURE sp_crear_estudio_realizado(
    IN p_titulo VARCHAR(45),
    IN p_universidad VARCHAR(50),
    IN p_fecha DATE,
    IN p_tipo VARCHAR(45),
    IN p_ciudad VARCHAR(45),
    IN p_docente INT,
    IN p_ins_acreditada TINYINT,
    IN p_metodologia VARCHAR(45),
    IN p_perfil_egresado LONGTEXT,
    IN p_pais VARCHAR(45)
)
BEGIN
    DECLARE v_id INT;

    INSERT INTO estudios_realizados (
        titulo, universidad, fecha, tipo, ciudad, docente,
        ins_acreditada, metodologia, perfil_egresado, pais, activo
    ) VALUES (
        p_titulo, p_universidad, p_fecha, p_tipo, p_ciudad, p_docente,
        p_ins_acreditada, p_metodologia, p_perfil_egresado, p_pais, 1
    );

    SET v_id = LAST_INSERT_ID();
    SELECT * FROM estudios_realizados WHERE id = v_id;
END$$

-- 10. Actualizar estudio realizado
CREATE OR REPLACE PROCEDURE sp_actualizar_estudio_realizado(
    IN p_id INT,
    IN p_titulo VARCHAR(45),
    IN p_universidad VARCHAR(50),
    IN p_fecha DATE,
    IN p_tipo VARCHAR(45),
    IN p_ciudad VARCHAR(45),
    IN p_docente INT,
    IN p_ins_acreditada TINYINT,
    IN p_metodologia VARCHAR(45),
    IN p_perfil_egresado LONGTEXT,
    IN p_pais VARCHAR(45)
)
BEGIN
    UPDATE estudios_realizados SET
        titulo = p_titulo,
        universidad = p_universidad,
        fecha = p_fecha,
        tipo = p_tipo,
        ciudad = p_ciudad,
        docente = p_docente,
        ins_acreditada = p_ins_acreditada,
        metodologia = p_metodologia,
        perfil_egresado = p_perfil_egresado,
        pais = p_pais
    WHERE id = p_id AND activo = 1;
    SELECT * FROM estudios_realizados WHERE id = p_id;
END$$

-- 11. Eliminar (retirar) estudio realizado
CREATE OR REPLACE PROCEDURE sp_eliminar_estudio_realizado(
    IN p_id INT
)
BEGIN
    UPDATE estudios_realizados SET activo = 0 WHERE id = p_id;
END$$

DELIMITER ;
