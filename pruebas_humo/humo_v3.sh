#!/usr/bin/env bash
# =============================================================================
# Pruebas de humo v3 — Autenticación, JWT y control de acceso por roles.
# Cubre los criterios CA-01 a CA-07 y CA-10 (regresión de los 16 recursos v1+v2).
#
# Uso:   ./pruebas_humo/humo_v3.sh [URL_API]      (por defecto http://localhost:8113)
# Requiere: curl y php (solo para leer el JSON de las respuestas).
# =============================================================================
set -u
BASE="${1:-http://localhost:8113}"
OK=0; FALLOS=0

json() { php -r '$j=json_decode(stream_get_contents(STDIN),true); $v=$j; foreach (explode(".",$argv[1]) as $k) { $v=$v[$k]??null; } echo is_scalar($v)?$v:json_encode($v);' "$1"; }

# req METODO RUTA TOKEN [CUERPO]  -> deja el código en $COD y el cuerpo en $CUE
req() {
  local m="$1" r="$2" t="$3" b="${4:-}"
  local args=(-s -o /tmp/humo_v3_cuerpo -w '%{http_code}' -X "$m")
  [ -n "$t" ] && args+=(-H "Authorization: Bearer $t")
  [ -n "$b" ] && args+=(-H "Content-Type: application/json" -d "$b")
  COD=$(curl "${args[@]}" "$BASE$r"); CUE=$(cat /tmp/humo_v3_cuerpo)
}
# espera DESCRIPCION CODIGO_ESPERADO   (usa el $COD de la última petición)
espera() {
  if [ "$COD" = "$2" ]; then OK=$((OK+1)); echo "  OK   $1 ($COD)"
  else FALLOS=$((FALLOS+1)); echo "  FALLO $1 -> esperaba $2, obtuvo $COD  $CUE" | cut -c1-230; fi
}
token() { req POST /api/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; echo "$CUE" | json token; }

echo "=== CA-01 / CA-02: LOGIN ==="
req POST /api/login "" '{"username":"admin","password":"admin123"}'; espera "login válido" 200
echo "$CUE" | json roles | grep -q admin && { OK=$((OK+1)); echo "  OK   la respuesta trae el rol admin"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO sin roles en el login"; }
req POST /api/login "" '{"username":"admin","password":"mala"}';   espera "contraseña errónea" 401; M1="$CUE"
req POST /api/login "" '{"username":"noexiste","password":"x"}';   espera "usuario inexistente" 401; M2="$CUE"
[ "$M1" = "$M2" ] && { OK=$((OK+1)); echo "  OK   mensaje idéntico (sin enumeración de usuarios)"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO mensajes distintos"; }
req POST /api/login "" '{}'; espera "cuerpo vacío" 422

ADM=$(token admin admin123); DOC=$(token docente1 docente123); CON=$(token consultor1 consulta123)
[ -n "$ADM" ] && [ -n "$DOC" ] && [ -n "$CON" ] || { echo "No se pudieron obtener los tres tokens. Abortando."; exit 1; }

echo "=== CA-03 / CA-04: SIN TOKEN, TOKEN ADULTERADO ==="
req GET /api/docente "";                         espera "sin token" 401
curl -s -i "$BASE/api/docente" | grep -qi '^WWW-Authenticate: Bearer' && { OK=$((OK+1)); echo "  OK   cabecera WWW-Authenticate: Bearer"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO falta WWW-Authenticate"; }
req GET /api/docente "${ADM}x";                  espera "firma adulterada" 401
req GET /api/docente "abc.def";                  espera "token truncado" 401
PAY=$(echo -n '{"sub":"admin","roles":["admin"],"iat":1,"exp":2}' | base64 | tr '+/' '-_' | tr -d '=\n')
req GET /api/docente "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.$PAY.firmafalsa"; espera "token forjado/vencido" 401
req GET /api/sesion "$ADM";                      espera "GET /api/sesion" 200

echo "=== CA-05: ROL CONSULTA = SOLO LECTURA ==="
req GET  /api/programa "$CON";                                         espera "consulta lee (GET)" 200
req POST /api/linea-investigacion "$CON" '{"nombre":"X","descripcion":"Y"}'; espera "consulta POST" 403
req PUT  /api/linea-investigacion/1 "$CON" '{"nombre":"X","descripcion":"Y"}'; espera "consulta PUT" 403
req DELETE /api/linea-investigacion/1 "$CON";                          espera "consulta DELETE" 403
echo "$CUE" | grep -q "Su rol no tiene permiso para esta operacion." && { OK=$((OK+1)); echo "  OK   mensaje exacto del 403"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO mensaje 403 distinto"; }

echo "=== CA-06: ADMINISTRACIÓN SOLO ADMIN ==="
for r in usuario rol rol-usuario; do
  req GET /api/$r "$DOC"; espera "docente -> /api/$r" 403
  req GET /api/$r "$CON"; espera "consulta -> /api/$r" 403
  req GET /api/$r "$ADM"; espera "admin -> /api/$r" 200
done

echo "=== CA-10: REGRESIÓN — CRUD con rol docente sobre los 16 recursos ==="
T="$DOC"; U=$RANDOM
# --- v1 ---
req POST /api/linea-investigacion $T "{\"nombre\":\"Linea $U\",\"descripcion\":\"Prueba\"}"; espera "linea POST" 200
LIN=$(echo "$CUE" | json id); [ -z "$LIN" ] && LIN=$(echo "$CUE" | json datos.id)
req PUT   /api/linea-investigacion/$LIN $T '{"nombre":"Linea editada","descripcion":"Otra"}'; espera "linea PUT" 200
req PATCH /api/linea-investigacion/$LIN $T '{"descripcion":"Parcial"}';                      espera "linea PATCH" 200
req PUT   /api/linea-investigacion/$LIN $T '{"descripcion":"Falta nombre"}';                  espera "linea PUT incompleto" 422
req POST /api/termino-clave $T "{\"termino\":\"t$U\",\"termino_ingles\":\"term\"}";          espera "termino POST" 200
req PATCH /api/termino-clave/t$U $T '{"termino_ingles":"otro"}';                              espera "termino PATCH" 200
req POST /api/red $T "{\"idr\":$((U+50000)),\"nombre\":\"Red $U\",\"url\":\"https://r.org\",\"pais\":\"CO\"}"; espera "red POST" 200
req PATCH /api/red/$((U+50000)) $T '{"pais":"MX"}';                                           espera "red PATCH" 200
req POST /api/area-conocimiento $T "{\"id\":\"Z$((U%100))\",\"gran_area\":\"G\",\"area\":\"A\",\"disciplina\":\"D\"}"; espera "area POST" 200
req POST /api/programa $T "{\"id\":$((U+60000)),\"nombre\":\"Prog $U\",\"tipo\":\"Pregrado\",\"nivel\":\"Profesional\",\"fecha_creacion\":\"2020-01-01\",\"numero_cohortes\":\"1\",\"cant_graduados\":\"1\",\"fecha_actualizacion\":\"2026-01-01\",\"ciudad\":\"Medellin\",\"facultad\":1}"; espera "programa POST" 200
PRG=$((U+60000))
# --- v2 maestro-detalle ---
CED=$((90000000+U))
req POST /api/docente $T "{\"cedula\":$CED,\"nombres\":\"Ana\",\"apellidos\":\"Prueba\",\"genero\":\"Femenino\",\"cargo\":\"Profesor\",\"fecha_nacimiento\":\"1985-04-12\",\"correo\":\"a@u.co\",\"telefono\":\"300\",\"url_cvlac\":\"https://x\",\"fecha_actualizacion\":\"2026-03-01\",\"escalafon\":\"Asociado\",\"perfil\":\"P\",\"cat_minciencia\":\"S\",\"conv_minciencia\":\"894\",\"nacionalidaad\":\"Colombiana\",\"linea_investigacion_principal\":null,\"estudios\":[{\"titulo\":\"Ing\",\"universidad\":\"UdeA\",\"fecha\":\"2010-06-15\",\"tipo\":\"Pregrado\",\"ciudad\":\"Medellin\",\"ins_acreditada\":1,\"metodologia\":\"Presencial\",\"perfil_egresado\":\"x\",\"pais\":\"Colombia\"}]}"; espera "docente POST (maestro-detalle)" 200
req GET /api/docente/$CED $T; espera "docente GET uno" 200
echo "$CUE" | json estudios | grep -q titulo && { OK=$((OK+1)); echo "  OK   el docente trae sus estudios"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO el docente no trae estudios"; }
EST=$(echo "$CUE" | json estudios.0.id)
req PATCH /api/docente/$CED $T '{"cargo":"Titular"}';                      espera "docente PATCH" 200
req DELETE /api/docente/$CED $T;                                           espera "docente DELETE con estudios (regla de negocio)" 409
req POST /api/estudios-realizados $T '{"titulo":"X","universidad":"U","fecha":"2020-01-01","tipo":"Maestria","ciudad":"Cali","pais":"CO","metodologia":"P","ins_acreditada":0,"docente":99999999,"perfil_egresado":"x"}'; espera "estudio con docente inexistente" 409
req POST /api/evaluacion-docente $T "{\"calificacion\":4.5,\"semestre\":\"2026-1\",\"docente\":$CED}"; espera "evaluacion POST" 200
EVA=$(echo "$CUE" | json id); [ -z "$EVA" ] && EVA=$(echo "$CUE" | json datos.id)
req PATCH /api/evaluacion-docente/$EVA $T '{"calificacion":3.9}';                      espera "evaluacion PATCH" 200
req POST /api/experiencia $T "{\"nombre_cargo\":\"Jefe\",\"institucion\":\"U\",\"tipo\":\"Academica\",\"fecha_inicio\":\"2015-01-01\",\"docente\":$CED}"; espera "experiencia POST" 200
req POST /api/reconocimiento $T "{\"tipo\":\"Premio\",\"fecha\":\"2022-01-01\",\"institucion\":\"U\",\"nombre\":\"Mejor\",\"ambito\":\"Nacional\",\"docente\":$CED}"; espera "reconocimiento POST" 200
req POST /api/docente-departamento $T "{\"docente\":$CED,\"departamento\":$PRG,\"dedicacion\":\"Tiempo completo\",\"modalidad\":\"Presencial\",\"fecha_ingreso\":\"2020-01-01\"}"; espera "docente-departamento POST" 200
req GET  /api/docente-departamento/$CED/$PRG $T;                                       espera "docente-departamento GET compuesta" 200
req PATCH /api/docente-departamento/$CED/$PRG $T '{"modalidad":"Virtual"}';           espera "docente-departamento PATCH" 200
req POST /api/intereses-futuros $T "{\"docente\":$CED,\"termino_clave\":\"t$U\"}";     espera "intereses-futuros POST" 200
req POST /api/red-docente $T "{\"red\":$((U+50000)),\"docente\":$CED,\"fecha_inicio\":\"2021-01-01\",\"act_destacadas\":\"Algo\"}"; espera "red-docente POST" 200
req PATCH /api/red-docente/$((U+50000))/$CED $T '{"act_destacadas":"Mas"}';           espera "red-docente PATCH" 200
req POST /api/estudio-ac $T "{\"estudio\":$EST,\"area_conocimiento\":\"1A01\"}";       espera "estudio-ac POST" 200
req POST /api/apoyo-profesoral $T "{\"estudios\":$EST,\"con_apoyo\":1,\"institucion\":\"U\",\"tipo\":\"Total\"}"; espera "apoyo-profesoral POST" 200
req PATCH /api/apoyo-profesoral/$EST $T '{"tipo":"Parcial"}';                          espera "apoyo-profesoral PATCH" 200
req POST /api/beca $T "{\"estudios\":$EST,\"tipo\":\"Doctoral\",\"institucion\":\"Minciencias\",\"fecha_inicio\":\"2018-01-01\"}"; espera "beca POST" 200
req PATCH /api/beca/$EST $T '{"tipo":"Maestria"}';                                     espera "beca PATCH" 200
echo "  -- limpieza (retiro lógico en orden inverso)"
req DELETE /api/beca/$EST $T;                       espera "beca DELETE" 200
req DELETE /api/apoyo-profesoral/$EST $T;           espera "apoyo DELETE" 200
req DELETE /api/estudio-ac/$EST/1A01 $T;            espera "estudio-ac DELETE" 200
req DELETE /api/red-docente/$((U+50000))/$CED $T;   espera "red-docente DELETE" 200
req DELETE /api/intereses-futuros/$CED/t$U $T;      espera "intereses-futuros DELETE" 200
req DELETE /api/docente-departamento/$CED/$PRG $T;  espera "docente-departamento DELETE" 200
req DELETE /api/evaluacion-docente/$EVA $T;         espera "evaluacion DELETE" 200
req GET /api/evaluacion-docente/$EVA $T;            espera "evaluacion retirada ya no se ve (404)" 404
req DELETE /api/estudios-realizados/$EST $T;        espera "estudio DELETE" 200
req DELETE /api/linea-investigacion/$LIN $T;        espera "linea DELETE" 200
req DELETE /api/programa/$PRG $T;                   espera "programa DELETE" 200

echo "=== ADMIN: usuarios, roles y asignación ==="
req POST /api/usuario "$ADM" "{\"username\":\"u$U\",\"password\":\"clave12345\",\"email\":\"u$U@u.co\",\"nombre_completo\":\"Prueba $U\"}"; espera "usuario POST" 201
UID_=$(echo "$CUE" | json datos.id)
req POST /api/usuario "$ADM" "{\"username\":\"u$U\",\"password\":\"clave12345\",\"email\":\"otro$U@u.co\"}"; espera "usuario duplicado" 409
req POST /api/rol-usuario "$ADM" "{\"usuario_id\":$UID_,\"rol_id\":3}"; espera "asignar rol" 201
req POST /api/rol-usuario "$ADM" "{\"usuario_id\":$UID_,\"rol_id\":3}"; espera "asignar rol repetido" 409
NUEVO=$(token u$U clave12345); [ -n "$NUEVO" ] && { OK=$((OK+1)); echo "  OK   el usuario nuevo inicia sesión (hash BCRYPT verificado)"; } || { FALLOS=$((FALLOS+1)); echo "  FALLO el usuario nuevo no inicia sesión"; }
req GET /api/roles-xx "$ADM"; espera "recurso inexistente" 404
req DELETE /api/rol-usuario "$ADM" "{\"usuario_id\":$UID_,\"rol_id\":3}"; espera "desasignar rol" 200
req DELETE /api/usuario/$UID_ "$ADM"; espera "usuario DELETE (lógico)" 200
req POST /api/login "" "{\"username\":\"u$U\",\"password\":\"clave12345\"}"; espera "usuario retirado no inicia sesión" 401

echo "=== CA-09: LOGOUT ==="
req DELETE /api/sesion "$ADM"; espera "DELETE /api/sesion" 200

echo
echo "RESULTADO: $OK pruebas OK, $FALLOS fallos"
[ "$FALLOS" -eq 0 ] && echo "TODAS LAS PRUEBAS V3 EN VERDE" || exit 1
