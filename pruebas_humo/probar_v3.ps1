# =============================================================================
# Suite de Pruebas de Humo V3 — Gestión Profesoral (PowerShell)
# Autenticación, JWT artesanal y Control de Acceso por Roles (RBAC)
# =============================================================================
$ErrorActionPreference = "Continue"

$baseUrlApi = "http://localhost:8113"
$baseUrlFront = "http://localhost:8112"
$ok = 0
$fallos = 0

function Probar-Caso($descripcion, $condicion, $detalle = "") {
    if ($condicion) {
        $global:ok++
        Write-Host "  [OK] $descripcion" -ForegroundColor Green
    } else {
        $global:fallos++
        Write-Host "  [FALLÓ] $descripcion -> $detalle" -ForegroundColor Red
    }
}

function Peticion-Http($metodo, $ruta, $token = $null, $body = $null) {
    try {
        $headers = @{"Content-Type" = "application/json"}
        if ($token) {
            $headers["Authorization"] = "Bearer $token"
        }
        $params = @{
            Uri = "$baseUrlApi$ruta"
            Method = $metodo
            Headers = $headers
            TimeoutSec = 10
        }
        if ($body) {
            $params["Body"] = ($body | ConvertTo-Json -Depth 10)
        }
        $resp = Invoke-WebRequest @params -SkipHttpErrorCheck
        $json = $null
        try {
            $json = $resp.Content | ConvertFrom-Json
        } catch {}

        return @{
            StatusCode = [int]$resp.StatusCode
            Headers = $resp.Headers
            Content = $resp.Content
            Json = $json
        }
    } catch {
        return @{
            StatusCode = 0
            Headers = @{}
            Content = $_.Exception.Message
            Json = $null
        }
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  INICIANDO SUITE DE PRUEBAS DE HUMO V3 (SEGURIDAD Y RBAC)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# -----------------------------------------------------------------------------
# 1. Diagnóstico
# -----------------------------------------------------------------------------
Write-Host "`n--- 1. DIAGNÓSTICO PÚBLICO ---" -ForegroundColor Yellow
$diag = Peticion-Http "GET" "/"
Probar-Caso "GET / (Diagnóstico público)" ($diag.StatusCode -eq 200 -and $diag.Json.version -eq "v3") "Código $($diag.StatusCode)"

# -----------------------------------------------------------------------------
# 2. CA-01 y CA-02: Login y Prevención de Enumeración
# -----------------------------------------------------------------------------
Write-Host "`n--- 2. AUTENTICACIÓN Y PREVENCIÓN DE ENUMERACIÓN ---" -ForegroundColor Yellow
$loginAdmin = Peticion-Http "POST" "/api/login" -body @{ username = "admin"; password = "admin123" }
Probar-Caso "Login válido (admin / admin123) responde 200" ($loginAdmin.StatusCode -eq 200 -and $loginAdmin.Json.token) "Código $($loginAdmin.StatusCode)"
Probar-Caso "Login trae rol 'admin'" ($loginAdmin.Json.roles -contains "admin")

$tokenAdmin = $loginAdmin.Json.token

$loginMala = Peticion-Http "POST" "/api/login" -body @{ username = "admin"; password = "clave_incorrecta" }
Probar-Caso "Login con clave errónea responde 401" ($loginMala.StatusCode -eq 401) "Código $($loginMala.StatusCode)"

$loginNoExiste = Peticion-Http "POST" "/api/login" -body @{ username = "usuario_inexistente_xyz"; password = "password" }
Probar-Caso "Login con usuario inexistente responde 401" ($loginNoExiste.StatusCode -eq 401) "Código $($loginNoExiste.StatusCode)"

Probar-Caso "Mensaje uniforme (previene enumeración de usuarios)" ($loginMala.Json.mensaje -eq $loginNoExiste.Json.mensaje) "Mensajes diferentes"

$loginVacio = Peticion-Http "POST" "/api/login" -body @{}
Probar-Caso "Login con cuerpo vacío responde 422" ($loginVacio.StatusCode -eq 422) "Código $($loginVacio.StatusCode)"

# -----------------------------------------------------------------------------
# 3. Obtención de tokens para los 3 roles
# -----------------------------------------------------------------------------
Write-Host "`n--- 3. VERIFICACIÓN DE TOKENS POR ROL ---" -ForegroundColor Yellow
$loginDocente = Peticion-Http "POST" "/api/login" -body @{ username = "docente1"; password = "docente123" }
$tokenDocente = $loginDocente.Json.token
Probar-Caso "Token obtenido para docente1 (rol docente)" ($tokenDocente -and $loginDocente.Json.roles -contains "docente")

$loginConsulta = Peticion-Http "POST" "/api/login" -body @{ username = "consultor1"; password = "consulta123" }
$tokenConsulta = $loginConsulta.Json.token
Probar-Caso "Token obtenido para consultor1 (rol consulta)" ($tokenConsulta -and $loginConsulta.Json.roles -contains "consulta")

# -----------------------------------------------------------------------------
# 4. CA-03 y CA-04: Protección contra peticiones anónimas o tokens adulterados
# -----------------------------------------------------------------------------
Write-Host "`n--- 4. GUARDIA DE SEGURIDAD (401 UNAUTHORIZED) ---" -ForegroundColor Yellow
$sinToken = Peticion-Http "GET" "/api/docente"
Probar-Caso "Petición a recurso protegido sin token responde 401" ($sinToken.StatusCode -eq 401) "Código $($sinToken.StatusCode)"

$cabeceraAuth = $sinToken.Headers["WWW-Authenticate"]
Probar-Caso "Respuesta 401 incluye cabecera WWW-Authenticate: Bearer" ($cabeceraAuth -like "*Bearer*") "Cabecera: $cabeceraAuth"

$tokenAdulterado = Peticion-Http "GET" "/api/docente" -token ($tokenAdmin + "x")
Probar-Caso "Token con firma adulterada responde 401" ($tokenAdulterado.StatusCode -eq 401) "Código $($tokenAdulterado.StatusCode)"

$tokenTruncado = Peticion-Http "GET" "/api/docente" -token "header.payload"
Probar-Caso "Token con formato incompleto responde 401" ($tokenTruncado.StatusCode -eq 401) "Código $($tokenTruncado.StatusCode)"

$sesion = Peticion-Http "GET" "/api/sesion" -token $tokenAdmin
Probar-Caso "GET /api/sesion con token válido responde 200 y datos de usuario" ($sesion.StatusCode -eq 200 -and $sesion.Json.username -eq "admin")

# -----------------------------------------------------------------------------
# 5. CA-05: Rol Consulta = Solo Lectura (403 en escritura)
# -----------------------------------------------------------------------------
Write-Host "`n--- 5. AISLAMIENTO ROL CONSULTA (SOLO LECTURA) ---" -ForegroundColor Yellow
$consultaGet = Peticion-Http "GET" "/api/docente" -token $tokenConsulta
Probar-Caso "Rol consulta: GET /api/docente permitido (200)" ($consultaGet.StatusCode -eq 200) "Código $($consultaGet.StatusCode)"

$consultaPost = Peticion-Http "POST" "/api/programa" -token $tokenConsulta -body @{
    id = 99999; nombre = "Intento No Autorizado"; tipo = "Pregrado"; nivel = "Pregrado"
    fecha_creacion = "2026-01-01"; numero_cohortes = "1"; cant_graduados = "0"
    fecha_actualizacion = "2026-01-01"; ciudad = "Medellin"; facultad = 101
}
Probar-Caso "Rol consulta: POST bloqueado con 403 Forbidden" ($consultaPost.StatusCode -eq 403) "Código $($consultaPost.StatusCode)"

$consultaDelete = Peticion-Http "DELETE" "/api/red/1" -token $tokenConsulta
Probar-Caso "Rol consulta: DELETE bloqueado con 403 Forbidden" ($consultaDelete.StatusCode -eq 403) "Código $($consultaDelete.StatusCode)"

# -----------------------------------------------------------------------------
# 6. CA-06: Recursos Administrativos Exclusivos para Admin (403 para otros roles)
# -----------------------------------------------------------------------------
Write-Host "`n--- 6. RESTRICCIÓN DE USUARIOS Y ROLES (SOLO ADMIN) ---" -ForegroundColor Yellow
$docenteUsuarios = Peticion-Http "GET" "/api/usuario" -token $tokenDocente
Probar-Caso "Rol docente -> GET /api/usuario bloqueado con 403" ($docenteUsuarios.StatusCode -eq 403) "Código $($docenteUsuarios.StatusCode)"

$consultaRoles = Peticion-Http "GET" "/api/rol" -token $tokenConsulta
Probar-Caso "Rol consulta -> GET /api/rol bloqueado con 403" ($consultaRoles.StatusCode -eq 403) "Código $($consultaRoles.StatusCode)"

$adminUsuarios = Peticion-Http "GET" "/api/usuario" -token $tokenAdmin
Probar-Caso "Rol admin -> GET /api/usuario permitido (200)" ($adminUsuarios.StatusCode -eq 200 -and $adminUsuarios.Json.datos.Count -ge 3)

$adminRoles = Peticion-Http "GET" "/api/rol" -token $tokenAdmin
Probar-Caso "Rol admin -> GET /api/rol permitido (200)" ($adminRoles.StatusCode -eq 200 -and $adminRoles.Json.datos.Count -ge 3)

# -----------------------------------------------------------------------------
# 7. CA-07 y CRUD Admin: Creación, Duplicados y Asignación de Roles
# -----------------------------------------------------------------------------
Write-Host "`n--- 7. CRUD ADMINISTRATIVO (CREACIÓN Y REGLAS) ---" -ForegroundColor Yellow
$rnd = Get-Random -Minimum 1000 -Maximum 9999
$nuevoUserPayload = @{
    username = "testuser_$rnd"
    password = "password123"
    email = "test_$rnd@universidad.edu.co"
    nombre_completo = "Usuario de Prueba $rnd"
}
$crearUser = Peticion-Http "POST" "/api/usuario" -token $tokenAdmin -body $nuevoUserPayload
Probar-Caso "Crear nuevo usuario responde 201 Created" ($crearUser.StatusCode -eq 201 -and $crearUser.Json.datos.id) "Código $($crearUser.StatusCode)"

$nuevoUserId = $crearUser.Json.datos.id

$duplicadoUser = Peticion-Http "POST" "/api/usuario" -token $tokenAdmin -body $nuevoUserPayload
Probar-Caso "Username duplicado responde 409 Conflict" ($duplicadoUser.StatusCode -eq 409) "Código $($duplicadoUser.StatusCode)"

$asignarRol = Peticion-Http "POST" "/api/rol-usuario" -token $tokenAdmin -body @{
    usuario_id = $nuevoUserId
    rol_id = 2 # Rol docente
}
Probar-Caso "Asignar rol a usuario responde 201 Created" ($asignarRol.StatusCode -eq 201) "Código $($asignarRol.StatusCode)"

$asignarRepetido = Peticion-Http "POST" "/api/rol-usuario" -token $tokenAdmin -body @{
    usuario_id = $nuevoUserId
    rol_id = 2
}
Probar-Caso "Asignar rol repetido responde 409 Conflict" ($asignarRepetido.StatusCode -eq 409) "Código $($asignarRepetido.StatusCode)"

# -----------------------------------------------------------------------------
# 8. Cierre de Sesión (Logout)
# -----------------------------------------------------------------------------
Write-Host "`n--- 8. CIERRE DE SESIÓN ---" -ForegroundColor Yellow
$logout = Peticion-Http "DELETE" "/api/sesion" -token $tokenAdmin
Probar-Caso "DELETE /api/sesion responde 200 OK" ($logout.StatusCode -eq 200) "Código $($logout.StatusCode)"

# -----------------------------------------------------------------------------
# Resumen
# -----------------------------------------------------------------------------
Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "  RESUMEN: $ok EXITOSAS, $fallos FALLIDAS" -ForegroundColor $(if ($fallos -eq 0) { "Green" } else { "Red" })
Write-Host "==========================================================" -ForegroundColor Cyan
