# Script de Pruebas de Humo V2 — Gestión Profesoral (PowerShell)
$ErrorActionPreference = "Continue"

$baseUrlApi = "http://localhost:8113"
$baseUrlFront = "http://localhost:8112"

function Probar-Endpoint($nombre, $metodo, $url, $body = $null, $codigoEsperado = 200) {
    Write-Host "Ejecutando: $nombre [$metodo $url]..." -NoNewline
    try {
        $headers = @{"Content-Type" = "application/json"}
        $params = @{
            Uri = $url
            Method = $metodo
            Headers = $headers
            TimeoutSec = 10
        }
        if ($body) {
            $params["Body"] = ($body | ConvertTo-Json -Depth 10)
        }
        $resp = Invoke-WebRequest @params -SkipHttpErrorCheck
        $statusCode = [int]$resp.StatusCode

        if ($statusCode -eq $codigoEsperado) {
            Write-Host " [OK $statusCode]" -ForegroundColor Green
            return $resp.Content
        } else {
            Write-Host " [FALLÓ: Esperaba $codigoEsperado, obtuvo $statusCode]" -ForegroundColor Red
            Write-Host "Respuesta: $($resp.Content)" -ForegroundColor Yellow
            return $null
        }
    } catch {
        Write-Host " [ERROR DE CONEXION: $($_.Exception.Message)]" -ForegroundColor Red
        return $null
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  INICIANDO SUITE DE PRUEBAS DE HUMO V2                   " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Diagnóstico
Probar-Endpoint "Diagnóstico API V2" "GET" "$baseUrlApi/" -codigoEsperado 200

# 2. Creación Maestro-Detalle Atómica (1 docente + 2 estudios)
$cedulaPrueba = 1098765432
$docentePayload = @{
    cedula = $cedulaPrueba
    nombres = "Docente"
    apellidos = "Prueba Maestro-Detalle"
    genero = "Masculino"
    cargo = "Profesor Titular"
    fecha_nacimiento = "1978-08-20"
    correo = "docente.prueba@universidad.edu.co"
    telefono = "3007654321"
    url_cvlac = "https://scienti.minciencias.gov.co/cvlac/1098765432"
    fecha_actualizacion = "2026-03-01"
    escalafon = "Titular"
    perfil = "Doctor en Computación e Inteligencia Artificial."
    cat_minciencia = "Senior"
    conv_minciencia = "894"
    nacionalidaad = "Colombiana"
    linea_investigacion_principal = $null
    estudios = @(
        @{
            titulo = "Ingeniería de Sistemas"
            universidad = "Universidad de Antioquia"
            fecha = "2000-12-15"
            tipo = "Pregrado"
            ciudad = "Medellín"
            pais = "Colombia"
            metodologia = "Presencial"
            ins_acreditada = 1
            perfil_egresado = "Perfil en desarrollo y arquitectura de software"
        },
        @{
            titulo = "Doctorado en Computación"
            universidad = "Universidad de Chile"
            fecha = "2008-06-20"
            tipo = "Doctorado"
            ciudad = "Santiago"
            pais = "Chile"
            metodologia = "Presencial"
            ins_acreditada = 1
            perfil_egresado = "Investigación avanzada en algoritmos"
        }
    )
}

$respCrear = Probar-Endpoint "Crear Docente con Estudios (Envío Único)" "POST" "$baseUrlApi/api/docente" -body $docentePayload -codigoEsperado 200

# 3. Consulta del Docente con Detalle
$respConsulta = Probar-Endpoint "Consultar Docente y verificar sus Estudios" "GET" "$baseUrlApi/api/docente/$cedulaPrueba" -codigoEsperado 200

# 4. REGLA DE NEGOCIO: Retiro denegado de Docente con Estudios Activos (HTTP 409)
Probar-Endpoint "Validar Regla Negocio: Denegar Retiro de Docente con Estudios Activos (409)" "DELETE" "$baseUrlApi/api/docente/$cedulaPrueba" -codigoEsperado 409

# 5. INTEGRIDAD REFERENCIAL: Crear Estudio con Docente Inexistente (HTTP 409)
$estudioInvalido = @{
    titulo = "Especialización Invalida"
    universidad = "Universidad X"
    fecha = "2022-01-01"
    tipo = "Especialización"
    ciudad = "Cali"
    pais = "Colombia"
    metodologia = "Virtual"
    ins_acreditada = 0
    docente = 99999999
}
Probar-Endpoint "Integridad Referencial: Crear Estudio con Docente Inexistente (409)" "POST" "$baseUrlApi/api/estudios-realizados" -body $estudioInvalido -codigoEsperado 409

# 6. Tablas Dependientes V2 con FK
$evaluacionPayload = @{
    calificacion = 4.85
    semestre = "2026-1"
    docente = $cedulaPrueba
}
Probar-Endpoint "Crear Evaluación Docente con FK" "POST" "$baseUrlApi/api/evaluacion-docente" -body $evaluacionPayload -codigoEsperado 200

$experienciaPayload = @{
    nombre_cargo = "Líder de Arquitectura"
    institucion = "Empresa Tecnológica"
    tipo = "Profesional"
    fecha_inicio = "2015-02-01"
    fecha_fin = $null
    docente = $cedulaPrueba
}
Probar-Endpoint "Crear Experiencia con FK" "POST" "$baseUrlApi/api/experiencia" -body $experienciaPayload -codigoEsperado 200

$reconocimientoPayload = @{
    tipo = "Premio a la Excelencia"
    fecha = "2024-11-15"
    institucion = "Ministerio de Ciencia"
    nombre = "Medalla al Mérito Investigativo"
    ambito = "Nacional"
    docente = $cedulaPrueba
}
Probar-Endpoint "Crear Reconocimiento con FK" "POST" "$baseUrlApi/api/reconocimiento" -body $reconocimientoPayload -codigoEsperado 200

# 7. Retirar los estudios del docente de prueba
if ($respConsulta) {
    $docenteObj = $respConsulta | ConvertFrom-Json
    if ($docenteObj.estudios) {
        foreach ($est in $docenteObj.estudios) {
            Probar-Endpoint "Retirar Estudio ID $($est.id)" "DELETE" "$baseUrlApi/api/estudios-realizados/$($est.id)" -codigoEsperado 200
        }
    }
}

# 8. Ahora que no tiene estudios, el retiro del docente DEBE ser exitoso (HTTP 200)
Probar-Endpoint "Retirar Docente sin Estudios Activos (200)" "DELETE" "$baseUrlApi/api/docente/$cedulaPrueba" -codigoEsperado 200

# 9. REGRESIÓN OBLIGATORIA V1
Write-Host "`n--- REGRESIÓN DE RECURSOS V1 ---" -ForegroundColor Magenta
Probar-Endpoint "V1: Listar Programas Académicos" "GET" "$baseUrlApi/api/programa?limite=5" -codigoEsperado 200
Probar-Endpoint "V1: Listar Áreas de Conocimiento" "GET" "$baseUrlApi/api/area-conocimiento?limite=5" -codigoEsperado 200
Probar-Endpoint "V1: Listar Redes Académicas" "GET" "$baseUrlApi/api/red?limite=5" -codigoEsperado 200
Probar-Endpoint "V1: Listar Líneas de Investigación" "GET" "$baseUrlApi/api/linea-investigacion?limite=5" -codigoEsperado 200
Probar-Endpoint "V1: Listar Términos Clave" "GET" "$baseUrlApi/api/termino-clave?limite=5" -codigoEsperado 200

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  SUITE COMPLETADA                                        " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
