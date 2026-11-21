<?php
declare(strict_types=1);
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow border-0 rounded-lg">
            <div class="card-header bg-dark text-white text-center py-4">
                <h4 class="mb-1 font-weight-bold">Gestión Profesoral</h4>
                <p class="small text-muted mb-0">Control de Acceso y Sesión (v3)</p>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($mensajeError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error:</strong> <?php echo htmlspecialchars($mensajeError); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($mensajeExito)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($mensajeExito); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/entrar">
                    <div class="form-group mb-3">
                        <label for="username" class="font-weight-bold text-secondary">Usuario</label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   placeholder="Ej: admin, docente1, consultor1"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold text-secondary">Contraseña</label>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Ingrese su contraseña"
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm">
                        Iniciar Sesión
                    </button>
                </form>

                <hr class="my-4">

                <div class="alert alert-info small mb-0">
                    <div class="font-weight-bold mb-1">Cuentas de prueba preconfiguradas:</div>
                    <ul class="mb-0 pl-3">
                        <li><strong>admin</strong> / <code>admin123</code> (Acceso Total)</li>
                        <li><strong>docente1</strong> / <code>docente123</code> (Académico)</li>
                        <li><strong>consultor1</strong> / <code>consulta123</code> (Solo Lectura)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
