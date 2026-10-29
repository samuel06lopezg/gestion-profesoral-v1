# Guía de Inicio Rápido — v2 (Gestión Profesoral)

## 1. Levantar el Entorno con Docker

```bash
cd Gestion_Profesoral

# Si se modificó db/init.sql y se requiere reconstruir la base limpia:
docker compose down -v
docker compose build
docker compose up -d
```

## 2. Verificación de Salud de los Servicios

```bash
# Diagnóstico de la API
curl -i http://localhost:8113/

# Diagnóstico del Frontend
curl -i http://localhost:8112/
```

## 3. Pruebas de Comprobación Manual de la v2

### Prueba 1: Crear Docente con Estudios (Maestro-Detalle en un solo envío)
```bash
curl -X POST http://localhost:8113/api/docente \
  -H "Content-Type: application/json" \
  -d '{
    "cedula": 98765432,
    "nombres": "Carlos Alberto",
    "apellidos": "Mendoza Ruiz",
    "genero": "Masculino",
    "cargo": "Profesor Asistente",
    "fecha_nacimiento": "1980-05-14",
    "correo": "carlos.mendoza@universidad.edu.co",
    "telefono": "3109876543",
    "url_cvlac": "https://scienti.minciencias.gov.co/cvlac/visualizador/98765432",
    "fecha_actualizacion": "2026-03-01",
    "escalafon": "Asistente",
    "perfil": "Especialista en Telemática y Redes de Comunicación.",
    "cat_minciencia": "Investigador Asociado",
    "conv_minciencia": "Convocatoria 894",
    "nacionalidaad": "Colombiana",
    "linea_investigacion_principal": null,
    "estudios": [
      {
        "titulo": "Ingeniero Electrónico",
        "universidad": "Universidad del Valle",
        "fecha": "2003-12-10",
        "tipo": "Pregrado",
        "ciudad": "Cali",
        "ins_acreditada": 1,
        "metodologia": "Presencial",
        "perfil_egresado": "Diseño de sistemas de telecomunicaciones.",
        "pais": "Colombia"
      }
    ]
  }'
```

### Prueba 2: Regla de Negocio — Intento de Retirar Docente con Estudios
```bash
# Debe responder HTTP 409 con mensaje en castellano
curl -i -X DELETE http://localhost:8113/api/docente/98765432
```
Respuesta esperada:
```http
HTTP/1.1 409 Conflict
Content-Type: application/json; charset=utf-8

{
  "estado": 409,
  "mensaje": "No se puede retirar el docente porque tiene estudios registrados."
}
```

### Prueba 3: Integridad Referencial — Crear Estudio con Padre Inexistente
```bash
# Debe responder HTTP 409
curl -i -X POST http://localhost:8113/api/estudios-realizados \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Maestría en TI",
    "universidad": "Universidad de los Andes",
    "fecha": "2020-06-20",
    "tipo": "Maestría",
    "ciudad": "Bogotá",
    "docente": 999999999,
    "ins_acreditada": 1,
    "metodologia": "Presencial",
    "perfil_egresado": "Gestión estratégica de TI.",
    "pais": "Colombia"
  }'
```

### Prueba 4: Retirar Estudio y Posterior Retiro del Docente
```bash
# 1. Retirar el estudio (usar el ID retornado en la creación)
curl -i -X DELETE http://localhost:8113/api/estudios-realizados/1

# 2. Ahora que no tiene estudios activos, el retiro del docente debe ser exitoso (200)
curl -i -X DELETE http://localhost:8113/api/docente/98765432
```
