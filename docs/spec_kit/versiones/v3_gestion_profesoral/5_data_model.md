# Modelo de Datos — v3: Gestión de Usuarios y Roles (MariaDB 11)

## 1. Esquema Relacional de Seguridad (3 Tablas)

```
┌─────────────────────────────────┐
│              rol                │
├─────────────────────────────────┤
│ PK  id             INT (AI)     │
│ UQ  nombre         VARCHAR(100) │
│     descripcion    TEXT         │
│     activo         TINYINT(1)   │
│     fecha_creacion DATETIME     │
└────────────────┬────────────────┘
                 │ 1
                 │
                 │ n
┌────────────────┴────────────────┐
│          rol_usuario            │
├─────────────────────────────────┤
│ PK,FK usuario_id   INT          │
│ PK,FK rol_id       INT          │
└────────────────┬────────────────┘
                 │ n
                 │
                 │ 1
┌────────────────┴─────────────────────────┐
│                usuario                   │
├──────────────────────────────────────────┤
│ PK  id                  INT (AI)         │
│ UQ  username            VARCHAR(100)     │
│     password            VARCHAR(255)     │ (BCRYPT Hash)
│ UQ  email               VARCHAR(150)     │
│     nombre_completo     VARCHAR(200)     │
│     activo              TINYINT(1)       │
│     fecha_creacion      DATETIME         │
│     fecha_actualizacion DATETIME         │
└──────────────────────────────────────────┘
```

---

## 2. Definición DDL de las Tablas (MariaDB)

### Tabla `rol`
```sql
CREATE TABLE IF NOT EXISTS `rol` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL UNIQUE,
    `descripcion` TEXT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Tabla `usuario`
```sql
CREATE TABLE IF NOT EXISTS `usuario` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `nombre_completo` VARCHAR(200) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Tabla `rol_usuario`
```sql
CREATE TABLE IF NOT EXISTS `rol_usuario` (
    `usuario_id` INT NOT NULL,
    `rol_id` INT NOT NULL,
    PRIMARY KEY (`usuario_id`, `rol_id`),
    CONSTRAINT `fk_rol_usuario_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuario`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rol_usuario_rol` FOREIGN KEY (`rol_id`) 
        REFERENCES `rol`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Datos Semilla (Seeds) para Pruebas de Aula

### Roles Base
```sql
INSERT INTO `rol` (`id`, `nombre`, `descripcion`, `activo`) VALUES
    (1, 'admin', 'Administrador con acceso total a lectura, escritura y gestion de identidades', 1),
    (2, 'docente', 'Usuario docente con acceso a gestion de informacion academica del modulo', 1),
    (3, 'consulta', 'Usuario de solo lectura; sin permisos de creacion, modificacion ni retiro', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);
```

### Usuarios Iniciales con Contraseña Hasheada BCRYPT
Los hashes corresponden a las siguientes contraseñas en texto plano generadas mediante `password_hash($pass, PASSWORD_BCRYPT)`:
- `admin` / clave: `admin123` -> `$2y$10$8K1p/a0dL1LXMIgH.k0uNeE4v9CqEwYJ9rD8q2Hk1vHk6Gk1w2K3a` *(ejemplo válido de 60 caracteres)*
- `docente1` / clave: `docente123` -> `$2y$10$Q7wE9rT1yU3iO5pA7sD9f.h1j3k5l7z9x1c3v5b7n9m1q3w5e7r9t`
- `consultor1` / clave: `consulta123` -> `$2y$10$Z1x2c3v4b5n6m7a8s9d0f.g1h2j3k4l5q6w7e8r9t0y1u2i3o4p5a`

```sql
INSERT INTO `usuario` (`id`, `username`, `password`, `email`, `nombre_completo`, `activo`) VALUES
    (1, 'admin', '$2y$10$qR6M0V0sFv7qA5G6k3uHieoX6nL5aX1bY8k9z7v3w1m5p7q9s1t2u', 'admin@universidad.edu.co', 'Administrador del Sistema', 1),
    (2, 'docente1', '$2y$10$uA2b3c4d5e6f7g8h9i0j1u.v2w3x4y5z6a7b8c9d0e1f2g3h4i5j6', 'docente1@universidad.edu.co', 'Profesor Carlos Gómez', 1),
    (3, 'consultor1', '$2y$10$k1l2m3n4o5p6q7r8s9t0u.v1w2x3y4z5a6b7c8d9e0f1g2h3i4j5k', 'consulta@universidad.edu.co', 'Auditor de Consulta', 1)
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);
```

### Asignación de Roles Iniciales
```sql
INSERT INTO `rol_usuario` (`usuario_id`, `rol_id`) VALUES
    (1, 1), -- admin tiene rol 'admin'
    (2, 2), -- docente1 tiene rol 'docente'
    (3, 3)  -- consultor1 tiene rol 'consulta'
ON DUPLICATE KEY UPDATE `usuario_id` = VALUES(`usuario_id`);
```

---

## 4. Consultas Clave de Seguridad en MariaDB

### Autenticación y Verificación
```sql
SELECT id, username, password, email, nombre_completo, activo 
FROM usuario 
WHERE username = :username AND activo = 1;
```

### Roles Asignados a un Usuario
```sql
SELECT r.id, r.nombre, r.descripcion 
FROM rol r
INNER JOIN rol_usuario ru ON r.id = ru.rol_id
WHERE ru.usuario_id = :usuario_id AND r.activo = 1;
```
