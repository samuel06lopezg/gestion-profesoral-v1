# Contratos de API — v2: Maestro-Detalle y Tablas con FK (Gestión Profesoral)

Formato: JSON (`Content-Type: application/json; charset=utf-8`).

---

## 1. Recurso Maestro: `/api/docente`

### `GET /api/docente`
- **Respuesta 200:**
```json
{
  "tabla": "docente",
  "limite": 1000,
  "total": 2,
  "datos": [
    {
      "cedula": 1017245,
      "nombres": "Ana María",
      "apellidos": "Torres Gómez",
      "genero": "Femenino",
      "cargo": "Profesor Asociado",
      "fecha_nacimiento": "1985-04-12",
      "correo": "ana.torres@universidad.edu.co",
      "telefono": "3001234567",
      "url_cvlac": "https://scienti.minciencias.gov.co/cvlac/visualizador/1017245",
      "fecha_actualizacion": "2026-03-01",
      "escalafon": "Asociado",
      "perfil": "Doctora en Computación especializada en Inteligencia Artificial.",
      "cat_minciencia": "Investigador Senior",
      "conv_minciencia": "Convocatoria 894",
      "nacionalidaad": "Colombiana",
      "linea_investigacion_principal": 1,
      "activo": 1
    }
  ]
}
```
- **Respuesta 204:** Sin contenido cuando no hay registros activos.

---

### `GET /api/docente/{cedula}`
- **Respuesta 200:** Retorna el docente y su arreglo de estudios realizados.
```json
{
  "cedula": 1017245,
  "nombres": "Ana María",
  "apellidos": "Torres Gómez",
  "genero": "Femenino",
  "cargo": "Profesor Asociado",
  "fecha_nacimiento": "1985-04-12",
  "correo": "ana.torres@universidad.edu.co",
  "telefono": "3001234567",
  "url_cvlac": "https://scienti.minciencias.gov.co/cvlac/visualizador/1017245",
  "fecha_actualizacion": "2026-03-01",
  "escalafon": "Asociado",
  "perfil": "Doctora en Computación.",
  "cat_minciencia": "Investigador Senior",
  "conv_minciencia": "Convocatoria 894",
  "nacionalidaad": "Colombiana",
  "linea_investigacion_principal": 1,
  "estudios": [
    {
      "id": 12,
      "titulo": "Doctorado en Ingeniería",
      "universidad": "Universidad Nacional",
      "fecha": "2018-11-20",
      "tipo": "Doctorado",
      "ciudad": "Medellín",
      "docente": 1017245,
      "ins_acreditada": 1,
      "metodologia": "Presencial",
      "perfil_egresado": "Investigación avanzada en sistemas.",
      "pais": "Colombia"
    }
  ]
}
```
- **Respuesta 404:** `{"estado": 404, "mensaje": "Docente no encontrado."}`

---

### `POST /api/docente` (Creación Maestro o Maestro-Detalle Atómico)
- **Cuerpo:**
```json
{
  "cedula": 1017245,
  "nombres": "Ana María",
  "apellidos": "Torres Gómez",
  "genero": "Femenino",
  "cargo": "Profesor Asociado",
  "fecha_nacimiento": "1985-04-12",
  "correo": "ana.torres@universidad.edu.co",
  "telefono": "3001234567",
  "url_cvlac": "https://scienti.minciencias.gov.co/cvlac/visualizador/1017245",
  "fecha_actualizacion": "2026-03-01",
  "escalafon": "Asociado",
  "perfil": "Doctora en Computación.",
  "cat_minciencia": "Investigador Senior",
  "conv_minciencia": "Convocatoria 894",
  "nacionalidaad": "Colombiana",
  "linea_investigacion_principal": null,
  "estudios": [
    {
      "titulo": "Ingeniería de Sistemas",
      "universidad": "Universidad de Antioquia",
      "fecha": "2010-06-15",
      "tipo": "Pregrado",
      "ciudad": "Medellín",
      "ins_acreditada": 1,
      "metodologia": "Presencial",
      "perfil_egresado": "Desarrollo de software y arquitectura.",
      "pais": "Colombia"
    }
  ]
}
```
- **Respuesta 200:** `{"estado": 200, "mensaje": "Docente creado exitosamente.", "cedula": 1017245}`
- **Respuesta 409:** Si la cédula ya existe (1062) o la línea de investigación no existe (1452).
- **Respuesta 422:** Si faltan campos obligatorios.

---

### `DELETE /api/docente/{cedula}` (Retiro Lógico con Regla de Negocio)
- **Respuesta 200:** `{"estado": 200, "mensaje": "Docente retirado exitosamente.", "filasAfectadas": 1}`
- **Respuesta 409 (Conflicto de Integridad / Negocio):**
```json
{
  "estado": 409,
  "mensaje": "No se puede retirar el docente porque tiene estudios registrados."
}
```

---

## 2. Recurso Detalle: `/api/estudios-realizados`

### `GET /api/estudios-realizados`
- Lista todos los estudios activos. Admite query parameter `?docente={cedula}` para filtrar por docente.

### `POST /api/estudios-realizados`
- Inserta un estudio individual para un docente existente.
- **Respuesta 409:** Si el `docente` no existe en el sistema (código 1452).

### `DELETE /api/estudios-realizados/{id}`
- Retira lógicamente el estudio (`activo = 0`).

---

## 3. Recursos con Claves Foráneas: `evaluacion-docente`, `experiencia`, `reconocimiento`

- Siguen los mismos verbos estándar REST:
  - `GET /api/{recurso}`: Listar activos.
  - `GET /api/{recurso}/{id}`: Consultar uno.
  - `POST /api/{recurso}`: Crear registro (valida FK existente; responde 409 si el docente no existe).
  - `PUT /api/{recurso}/{id}`: Reemplazar registro.
  - `PATCH /api/{recurso}/{id}`: Actualización parcial.
  - `DELETE /api/{recurso}/{id}`: Retiro lógico (`activo = 0`).
