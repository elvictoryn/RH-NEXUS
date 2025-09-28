<?php if (session_status()===PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container py-5" style="max-width:420px">
    <h3 class="mb-4 text-center">Nexus RH</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post"  id="loginForm" novalidate>
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control" required autofocus>
        <div class="invalid-feedback">Ingresa tu usuario.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" minlength="6" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePwd">👁</button>
        </div>
        <div class="form-text">Mínimo 6 caracteres.</div>
        <div class="invalid-feedback">Ingresa tu contraseña.</div>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label" for="remember">Recordar sesión (30 días)</label>
      </div>

      <button class="btn btn-primary w-100" type="submit">Entrar</button>
    </form>
  </div>

  <script>
    document.getElementById('togglePwd').addEventListener('click', function(){
      const i = document.getElementById('password');
      i.type = (i.type === 'password') ? 'text' : 'password';
    });
    (function(){
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', function(e){
        if(!form.checkValidity()){
          e.preventDefault(); e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    })();
  </script>
</body>
</html>
