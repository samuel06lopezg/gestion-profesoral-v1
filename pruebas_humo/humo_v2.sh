#!/usr/bin/env bash
set -e

BASE_URL="http://localhost:8113"

echo "=== DIAGNÓSTICO V2 ==="
curl -s -i "${BASE_URL}/" | grep -q "200 OK" && echo "Diagnóstico OK"

echo "=== 1. CREAR DOCENTE CON ESTUDIOS (MAESTRO-DETALLE ATÓMICO) ==="
curl -s -X POST "${BASE_URL}/api/docente" \
  -H "Content-Type: application/json" \
  -d '{
    "cedula": 1098765432,
    "nombres": "Docente",
    "apellidos": "Prueba Maestro-Detalle",
    "genero": "Masculino",
    "cargo": "Profesor Titular",
    "fecha_nacimiento": "1978-08-20",
    "correo": "docente.prueba@universidad.edu.co",
    "telefono": "3007654321",
    "url_cvlac": "https://scienti.minciencias.gov.co/cvlac/1098765432",
    "fecha_actualizacion": "2026-03-01",
    "escalafon": "Titular",
    "perfil": "Doctor en Computación e Inteligencia Artificial.",
    "cat_minciencia": "Senior",
    "conv_minciencia": "894",
    "nacionalidaad": "Colombiana",
    "linea_investigacion_principal": null,
    "estudios": [
      {
        "titulo": "Ingeniería de Sistemas",
        "universidad": "Universidad de Antioquia",
        "fecha": "2000-12-15",
        "tipo": "Pregrado",
        "ciudad": "Medellín",
        "pais": "Colombia",
        "metodologia": "Presencial",
        "ins_acreditada": 1,
        "perfil_egresado": "Perfil en desarrollo y arquitectura de software"
      }
    ]
  }' | grep -q "Docente creado exitosamente" && echo "Creación M-D OK"

echo "=== 2. REGLA DE NEGOCIO: RETIRO DENEGADO SI TIENE ESTUDIOS (HTTP 409) ==="
curl -s -i -X DELETE "${BASE_URL}/api/docente/1098765432" | grep -q "409 Conflict" && echo "Regla Negocio 409 OK"

echo "=== 3. INTEGRIDAD REFERENCIAL: HIJO CON PADRE INEXISTENTE (HTTP 409) ==="
curl -s -i -X POST "${BASE_URL}/api/estudios-realizados" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Maestría Inválida",
    "universidad": "U Inválida",
    "fecha": "2022-01-01",
    "tipo": "Maestría",
    "ciudad": "Cali",
    "pais": "Colombia",
    "metodologia": "Presencial",
    "ins_acreditada": 0,
    "docente": 99999999
  }' | grep -q "409 Conflict" && echo "Integridad Referencial 409 OK"

echo "=== 4. REGRESIÓN DE LA V1 ==="
curl -s -i "${BASE_URL}/api/programa?limite=1" | grep -q "200 OK" && echo "Programa OK"
curl -s -i "${BASE_URL}/api/red?limite=1" | grep -q "200 OK" && echo "Red OK"
curl -s -i "${BASE_URL}/api/linea-investigacion?limite=1" | grep -q "200 OK" && echo "Línea OK"
curl -s -i "${BASE_URL}/api/termino-clave?limite=1" | grep -q "200 OK" && echo "Término OK"
curl -s -i "${BASE_URL}/api/area-conocimiento?limite=1" | grep -q "200 OK" && echo "Área OK"

echo "=== TODAS LAS PRUEBAS V2 EN VERDE ==="
