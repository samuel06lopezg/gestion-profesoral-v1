#!/usr/bin/env python3
"""
Pruebas de humo del front web (v3) — solo biblioteca estándar de Python.

Recorre el sitio como lo haría un navegador (cookies de sesión, sin seguir
redirecciones) y comprueba: guardia de sesión, login/logout, menú por rol,
rol consulta de solo lectura, usuarios/roles solo admin y los formularios
principales (docente maestro-detalle, estudios, evaluaciones, etc.).

Uso:  python3 pruebas_humo/humo_front.py [URL_FRONT]   (por defecto http://localhost:8112)
"""
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8112").rstrip("/")
ok = fallos = 0


class SinRedireccion(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


class Navegador:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.op = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(self.jar), SinRedireccion()
        )

    def pedir(self, ruta, datos=None):
        cuerpo = None
        if datos is not None:
            cuerpo = urllib.parse.urlencode(datos, doseq=True).encode()
        try:
            r = self.op.open(urllib.request.Request(BASE + ruta, data=cuerpo), timeout=15)
            return r.status, r.headers, r.read().decode("utf-8", "replace")
        except urllib.error.HTTPError as e:
            return e.code, e.headers, e.read().decode("utf-8", "replace")


def comprobar(desc, condicion, detalle=""):
    global ok, fallos
    if condicion:
        ok += 1
        print(f"  OK    {desc}")
    else:
        fallos += 1
        print(f"  FALLO {desc} {detalle}"[:230])


def destino(h):
    return urllib.parse.unquote(h.get("Location", ""))


def entrar(usuario, clave):
    n = Navegador()
    st, h, _ = n.pedir("/entrar", {"username": usuario, "password": clave})
    return n, st, h


print("=== Guardia de sesión ===")
anonimo = Navegador()
for ruta in ["/", "/docentes", "/programas", "/usuarios", "/roles"]:
    st, h, _ = anonimo.pedir(ruta)
    comprobar(f"anónimo {ruta} -> redirige a /entrar", st == 302 and destino(h).startswith("/entrar"), f"{st} {destino(h)}")
st, _, html = anonimo.pedir("/entrar")
comprobar("el formulario de login se muestra", st == 200 and 'name="password"' in html)

print("=== Login ===")
n, st, h = entrar("admin", "mala")
comprobar("contraseña errónea -> 401 con mensaje", st == 401, st)
_, _, html = n.pedir("/entrar", {"username": "admin", "password": "mala"})
comprobar("mensaje genérico de credenciales", "El usuario o la contrasena no son correctos" in html)

print("=== Rol admin ===")
adm, st, h = entrar("admin", "admin123")
comprobar("login admin -> 302 a /", st == 302 and destino(h) == "/", f"{st} {destino(h)}")
st, _, html = adm.pedir("/")
comprobar("inicio muestra al usuario y su rol", st == 200 and "admin" in html and "Cerrar Sesión" in html)
comprobar("menú admin incluye Usuarios y Roles", 'href="/usuarios"' in html and 'href="/roles"' in html)
listas = ["/programas", "/redes", "/lineas", "/terminos", "/areas", "/docentes", "/estudios", "/evaluaciones",
          "/experiencias", "/reconocimientos", "/docente-departamentos", "/intereses-futuros", "/redes-docentes",
          "/estudios-areas", "/apoyos-profesorales", "/becas", "/usuarios", "/roles"]
for ruta in listas:
    st, _, html = adm.pedir(ruta)
    comprobar(f"admin GET {ruta}", st == 200 and "<table" in html or (st == 200 and "alert-info" in html), st)
for ruta in ["/docentes/nuevo", "/estudios/nuevo", "/evaluaciones/nueva", "/experiencias/nueva",
             "/reconocimientos/nuevo", "/usuarios/crear", "/roles/crear"]:
    st, _, html = adm.pedir(ruta)
    comprobar(f"admin GET formulario {ruta}", st == 200 and "<form" in html, st)
st, _, html = adm.pedir("/usuarios/crear")
comprobar("el formulario de usuario ofrece los roles", 'name="rol_ids[]"' in html)

print("=== Docente maestro-detalle (admin) ===")
ced = "91000777"
form_doc = {
    "cedula": ced, "nombres": "Prueba", "apellidos": "Front", "genero": "Femenino", "cargo": "Profesor",
    "fecha_nacimiento": "1985-04-12", "correo": "p@u.co", "telefono": "300", "url_cvlac": "https://x",
    "fecha_actualizacion": "2026-03-01", "escalafon": "Asociado", "perfil": "Perfil",
    "cat_minciencia": "", "conv_minciencia": "894", "nacionalidaad": "Colombiana",
    "linea_investigacion_principal": "",
    "detalle_titulo[]": ["Ing. Sistemas"], "detalle_universidad[]": ["UdeA"], "detalle_tipo[]": ["Pregrado"],
    "detalle_fecha[]": ["2010-06-15"], "detalle_ciudad[]": ["Medellín"], "detalle_pais[]": ["Colombia"],
    "detalle_metodologia[]": ["Presencial"], "detalle_ins_acreditada[]": ["1"], "detalle_perfil_egresado[]": ["x"],
}
st, h, _ = adm.pedir("/docentes/crear", form_doc)
comprobar("crear docente con un estudio", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
st, _, html = adm.pedir("/docentes")
comprobar("el docente aparece en la lista", ced in html)
st, _, html = adm.pedir(f"/docentes/editar?cedula={ced}")
comprobar("editar muestra el estudio registrado", st == 200 and "Ing. Sistemas" in html, st)
m = re.search(r"retirar-directo\?id=(\d+)", html)
id_estudio = m.group(1) if m else ""
comprobar("hay un id de estudio para retirar", bool(id_estudio))
form_ed = dict(form_doc, cargo="Titular")
form_ed.update({"detalle_titulo[]": ["Maestría X"], "detalle_tipo[]": ["Maestría"]})
st, h, _ = adm.pedir("/docentes/guardar-edicion", form_ed)
comprobar("editar docente + agregar un estudio", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
st, h, _ = adm.pedir("/docentes/retirar", {"cedula": ced})
comprobar("retirar docente con estudios -> mensaje de regla de negocio", st == 302 and "error" in destino(h), destino(h))

print("=== Entidades con FK a docente (admin) ===")
casos = [
    ("/estudios", "crear", {"titulo": "Esp", "universidad": "U", "fecha": "2020-01-01", "tipo": "Especialización",
                            "ciudad": "Cali", "pais": "Colombia", "metodologia": "Presencial", "ins_acreditada": "1",
                            "docente": ced, "perfil_egresado": "x"}),
    ("/evaluaciones", "crear", {"calificacion": "4.5", "semestre": "2026-1", "docente": ced}),
    ("/experiencias", "crear", {"nombre_cargo": "Jefe", "institucion": "U", "tipo": "Académica",
                                "fecha_inicio": "2015-01-01", "fecha_fin": "", "docente": ced}),
    ("/reconocimientos", "crear", {"tipo": "Premio", "fecha": "2022-01-01", "institucion": "U", "nombre": "Mejor",
                                   "ambito": "Nacional", "docente": ced}),
]
for base, _, datos in casos:
    st, h, _ = adm.pedir(base + "/crear", datos)
    comprobar(f"crear en {base}", st == 302 and "exito" in destino(h), f"{st} {destino(h)} ")
    st, _, html = adm.pedir(base)
    m = re.search(r'name="id" value="(\d+)"', html)
    comprobar(f"{base} lista el registro nuevo", bool(m))
    if m:
        rid = m.group(1)
        st, _, html = adm.pedir(f"{base}/editar?id={rid}")
        comprobar(f"formulario de edición de {base}", st == 200 and "<form" in html, st)
        st, h, _ = adm.pedir(base + "/guardar-edicion", dict(datos, id=rid))
        comprobar(f"modificar en {base}", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
        st, h, _ = adm.pedir(base + "/retirar", {"id": rid})
        comprobar(f"retirar en {base}", st == 302 and "exito" in destino(h), destino(h))
st, h, _ = adm.pedir(f"/estudios/retirar-directo?id={id_estudio}&docente={ced}")
comprobar("retirar estudio desde el formulario del docente", st == 302 and "exito" in destino(h), destino(h))

print("=== Usuarios y roles (admin) ===")
st, h, _ = adm.pedir("/roles/crear", {"nombre": "auditor_x", "descripcion": "Prueba"})
comprobar("crear rol", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
st, h, _ = adm.pedir("/usuarios/crear", {"username": "front_u1", "password": "clave12345", "email": "fu1@u.co",
                                         "nombre_completo": "Front Uno", "rol_ids[]": ["3"]})
comprobar("crear usuario con rol consulta", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
st, _, html = adm.pedir("/usuarios")
comprobar("la lista de usuarios muestra al nuevo con su rol", "front_u1" in html and "consulta" in html)
m = re.search(r"front_u1.*?editar\?id=(\d+)", html, re.S)
uid = m.group(1) if m else ""
st, h, _ = adm.pedir(f"/usuarios/editar?id={uid}", {"id": uid, "email": "fu1@u.co", "nombre_completo": "Front 1",
                                                    "password": "", "rol_ids[]": ["2"]})
comprobar("editar usuario y cambiar su rol a docente", st == 302 and "exito" in destino(h), f"{st} {destino(h)}")
st, _, html = adm.pedir("/usuarios")
fila = re.search(r"front_u1.*?</tr>", html, re.S)
comprobar("ahora tiene rol docente y ya no consulta",
          bool(fila) and "docente" in fila.group(0) and "consulta" not in fila.group(0))
st, h, _ = adm.pedir("/usuarios/editar?id=1", {"id": "1", "email": "admin@universidad.edu.co",
                                               "nombre_completo": "Admin", "password": "", "rol_ids[]": ["2"]})
comprobar("el admin no puede quitarse su propio rol admin", st == 200 and "mismo" in _.lower() or "No puede quitarse" in _, st)
nuevo, st, h = entrar("front_u1", "clave12345")
comprobar("el usuario creado desde el front inicia sesión", st == 302 and destino(h) == "/", f"{st}")
st, h, _ = adm.pedir("/usuarios/retirar", {"id": uid})
comprobar("retirar usuario", st == 302 and "exito" in destino(h), destino(h))
_, st, h = entrar("front_u1", "clave12345")
comprobar("el usuario retirado ya no inicia sesión", st == 401, st)

print("=== Rol docente ===")
doc, st, h = entrar("docente1", "docente123")
st, _, html = doc.pedir("/")
comprobar("docente NO ve Usuarios ni Roles en el menú", 'href="/usuarios"' not in html and 'href="/roles"' not in html)
st, _, _ = doc.pedir("/usuarios")
comprobar("docente GET /usuarios -> 403", st == 403, st)
st, _, _ = doc.pedir("/roles")
comprobar("docente GET /roles -> 403", st == 403, st)
st, _, html = doc.pedir("/programas")
comprobar("docente ve el botón de crear", st == 200 and "/programas/nuevo" in html)

print("=== Rol consulta ===")
con, st, h = entrar("consultor1", "consulta123")
st, _, html = con.pedir("/")
comprobar("consulta NO ve Usuarios ni Roles en el menú", 'href="/usuarios"' not in html and 'href="/roles"' not in html)
for ruta in ["/programas", "/docentes", "/estudios", "/becas"]:
    st, _, html = con.pedir(ruta)
    comprobar(f"consulta lee {ruta}", st == 200)
    comprobar(f"consulta: {ruta} oculta Crear/Editar/Retirar", "display: none !important" in html)
st, _, _ = con.pedir("/programas/nuevo")
comprobar("consulta GET /programas/nuevo -> 403", st == 403, st)
st, _, _ = con.pedir("/programas/crear", {"id": "1"})
comprobar("consulta POST /programas/crear -> 403", st == 403, st)
st, _, _ = con.pedir("/docentes/retirar", {"cedula": "1"})
comprobar("consulta POST /docentes/retirar -> 403", st == 403, st)
st, _, _ = con.pedir("/usuarios")
comprobar("consulta GET /usuarios -> 403", st == 403, st)

print("=== CA-09: Cierre de sesión ===")
st, h, _ = adm.pedir("/salir")
comprobar("/salir redirige a /entrar", st == 302 and destino(h).startswith("/entrar"), destino(h))
st, h, _ = adm.pedir("/docentes")
comprobar("tras salir, navegar redirige al login", st == 302 and destino(h).startswith("/entrar"), f"{st} {destino(h)}")

print(f"\nRESULTADO: {ok} OK, {fallos} fallos")
print("TODAS LAS PRUEBAS DEL FRONT EN VERDE" if fallos == 0 else "HAY FALLOS")
sys.exit(1 if fallos else 0)
