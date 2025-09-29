<?php if (session_status()===PHP_SESSION_NONE) session_start(); ?> 
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <style>
    /* =========================================================
       Paleta (puedes ajustar los tonos si lo deseas)
       ========================================================= */
    :root{
      --bg-1:#0d1b2a;   /* azul noche */
      --bg-2:#1b263b;   /* azul profundo */
      --sun:#ff9f68;    /* naranja atardecer */
      --teal:#09BC8A;   /* acento */
      --neon:#00e0ff;   /* brillo */
      --glass:#ffffff14;/* vidrio translúcido */
      --stroke:#ffffff33;
      --text:#eaf4ff;
      --muted:#cbd5e1;
      --primary:#0D6EFD;
    }

    /* ================== ESCENA DE FONDO ===================== */
    html,body{height:100%;}
    body{
      margin:0;
      display:flex;
      align-items:center;
      justify-content:center;
      color:var(--text);
      background:
        radial-gradient(1200px 700px at 75% 15%, #ffb27a33 0%, transparent 60%),
        radial-gradient(900px 600px at 20% 85%, #00e0ff22 0%, transparent 55%),
        linear-gradient(180deg, var(--bg-2) 0%, var(--bg-1) 100%);
      overflow:hidden;
      font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    /* Grano/ruido suave para dar textura */
    body::after{
      content:"";
      position:fixed; inset:0;
      pointer-events:none;
      background-image:
        radial-gradient(1px 1px at 10% 20%, #ffffff0d 0, transparent 100%),
        radial-gradient(1px 1px at 70% 40%, #ffffff0d 0, transparent 100%),
        radial-gradient(1px 1px at 30% 80%, #ffffff0d 0, transparent 100%);
      mix-blend-mode:overlay;
      opacity:.6;
      animation: floatDots 18s linear infinite;
    }
    @keyframes floatDots{
      0%{transform:translateY(0)}
      50%{transform:translateY(-20px)}
      100%{transform:translateY(0)}
    }

    /* “Sol” y reflejos (solo decorativo) */
    .orb, .orb-reflection{
      position:fixed; border-radius:50%; filter:blur(0.2px);
      box-shadow:0 0 40px 10px #ffb27a55, inset 0 0 30px #ffffff33;
    }
    .orb{
      width:110px; height:110px; right:9%; top:9%;
      background:radial-gradient(circle at 35% 35%, #fff6 0 25%, #ffb27a 40%, #ff7a4d 70%, #ff4d4d 100%);
    }
    .orb-reflection{
      width:90px; height:90px; right:12%; bottom:14%;
      background:radial-gradient(circle at 40% 40%, #fff5 0 25%, #ffb27a 45%, #ff7a4d 80%);
      opacity:.55;
    }

    /* Montañas abstractas con gradientes (muy ligeras) */
    .mounts, .mounts::before, .mounts::after{
      position:fixed; inset:auto; left:0; right:0; pointer-events:none;
      height:40vh; bottom:0;
      background:
        linear-gradient( to top, #0000 0 30%, #00000018 60%, #0000 100% ),
        radial-gradient(1200px 250px at 20% 100%, #143149 5%, #0e2437 60%, transparent 70%),
        radial-gradient(1000px 250px at 80% 100%, #143149 5%, #0e2437 60%, transparent 70%);
      opacity:.9;
    }
    .mounts::before{
      content:""; height:42vh; bottom:3%;
      background:
        radial-gradient(1100px 240px at 15% 100%, #1c3e5d 5%, #0f2b42 60%, transparent 70%),
        radial-gradient(900px 240px at 70% 100%, #1c3e5d 5%, #0f2b42 60%, transparent 70%);
      filter: blur(1px);
      opacity:.85;
    }
    .mounts::after{
      content:""; height:38vh; bottom:-2%;
      background:
        radial-gradient(1300px 220px at 35% 100%, #0b2031 5%, #081827 60%, transparent 70%),
        radial-gradient(1000px 220px at 90% 100%, #0b2031 5%, #081827 60%, transparent 70%);
      opacity:.9;
    }

    /* Reflejo “agua” */
    .lake{
      position:fixed; inset:auto; left:0; right:0; bottom:0; height:35vh;
      background:linear-gradient(180deg, #0000 0%, #0a1a28aa 35%, #07131e 100%);
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
    }

    /* ================== TARJETA DE VIDRIO ==================== */
    .wrap{width:min(92vw, 980px); padding:24px;}
    .glass{
      margin:auto;
      width:min(95vw, 540px);
      border-radius:24px;
      background:linear-gradient(180deg, #ffffff1f, #ffffff0d);
      border:1px solid var(--stroke);
      box-shadow:
        0 10px 40px rgba(0,0,0,.35),
        inset 0 1px 0 #ffffff25,
        inset 0 -1px 0 rgba(255,255,255,.08);
      backdrop-filter: blur(14px) saturate(120%);
      -webkit-backdrop-filter: blur(14px) saturate(120%);
      padding:28px 26px;
      position:relative;
    }

    .brand{
      display:flex; align-items:center; justify-content:center;
      gap:10px; margin-bottom:6px;
    }
    .brand .badge{
      width:36px;height:36px;border-radius:10px;
      background: radial-gradient(circle at 30% 30%, #ffffffaa 0 25%, #00e0ff 60%, #0D6EFD 100%);
      box-shadow: 0 0 20px #00e0ff55, inset 0 0 10px #ffffff66;
    }
    h3.title{
      text-align:center; margin:0; font-weight:700; letter-spacing:.3px;
      text-shadow:0 1px 0 #0004;
    }
    .subtitle{
      text-align:center; font-size:.95rem; color:var(--muted); margin-bottom:18px;
    }

    /* =============== Formularios =============== */
    label.form-label{ color:var(--muted); font-weight:600; }
    .form-control{
      background: #0b1928cc;
      border:1px solid #ffffff2e;
      color:var(--text);
      border-radius:14px;
      padding:0.85rem 1rem 0.85rem 2.6rem; /* espacio para icono */
      transition: border-color .2s, box-shadow .2s, transform .06s;
    }
    .form-control::placeholder{ color:#cbd5e199; }
    .form-control:focus{
      border-color:#00e0ff88;
      box-shadow:0 0 0 .2rem #00e0ff33, inset 0 0 12px #00e0ff11;
      outline: none;
    }
    .input-group .btn{
      border-radius:12px; border:1px solid #ffffff2e;
      background:#0b1928b3; color:#e5f2ff;
    }
    .input-group .btn:hover{ filter:brightness(1.1); }

    /* Iconos inline con data-URIs */
    .icon-user{
      background:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="%23cfe9ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>') no-repeat 12px center / 20px 20px;
    }
    .icon-lock{
      background:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="%23cfe9ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>') no-repeat 12px center / 20px 20px;
    }

    .form-text, .invalid-feedback{ color:#ffd9d9; }
    .form-check-input{
      background:#0b1928; border-color:#ffffff44;
    }
    .form-check-input:checked{
      background: linear-gradient(90deg, #0D6EFD, #09BC8A);
      border-color:transparent;
      box-shadow:0 0 0 .15rem #00e0ff33;
    }
    .form-check-label{ color:#e2edf8; }

    /* Botón principal estilo “neón vidrio” */
    .btn-primary{
      border:none;
      border-radius:14px;
      padding:.9rem 1rem;
      font-weight:700;
      background:linear-gradient(90deg, #0D6EFD, #09BC8A);
      color:#fff;
      box-shadow:
        0 8px 24px #0D6EFD40,
        inset 0 0 0 1px #ffffff33;
      transition: transform .08s ease, filter .2s ease, box-shadow .2s ease;
    }
    .btn-primary:hover{ filter:saturate(1.15) brightness(1.03); }
    .btn-primary:active{ transform: translateY(1px); }

    /* Alertas dentro del vidrio */
    .alert{
      border-radius:12px;
      background:#ff4d4d22;
      border:1px solid #ff9a9a55;
      color:#ffdede;
    }

    /* Footer mini */
    .foot{
      display:flex; justify-content:center; gap:8px; margin-top:10px;
      color:#cbd5e1cc; font-size:.9rem;
    }
    .foot a{ color:#aee9ff; text-decoration:none; }
    .foot a:hover{ text-decoration:underline; }

    /* Responsivo */
    @media (max-width:480px){
      .glass{ padding:22px 18px; }
      .glass::before{ left:12px; }
    }
  </style>
</head>

<body class="bg-light"> <!-- clase original, la sobreescribimos con CSS -->
  <!-- Elementos decorativos de la escena -->
  <span class="orb" aria-hidden="true"></span>
  <span class="orb-reflection" aria-hidden="true"></span>
  <div class="mounts" aria-hidden="true"></div>
  <div class="lake" aria-hidden="true"></div>

  <!-- Contenido -->
  <div class="wrap">
    <div class="glass">
      <div class="brand">
        <span class="badge"></span>
        <h3 class="title">Nexus RH</h3>
      </div>
      <p class="subtitle">Bienvenido, inicia sesión para continuar</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input type="text" name="usuario" class="form-control icon-user" required autofocus placeholder="Tu usuario">
          <div class="invalid-feedback">Ingresa tu usuario.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" name="password" id="password" class="form-control icon-lock" minlength="6" required placeholder="••••••••">
            <button class="btn" type="button" id="togglePwd" aria-label="Mostrar u ocultar contraseña">👁</button>
          </div>
          <div class="form-text">Mínimo 6 caracteres.</div>
          <div class="invalid-feedback">Ingresa tu contraseña.</div>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
          <label class="form-check-label" for="remember">Recordar sesión (30 días)</label>
        </div>

        <button class="btn btn-primary w-100" type="submit">Entrar</button>

        <div class="foot">
          <span>¿No tienes cuenta?</span>
          <a href="#" onclick="return false;">Contacta al administrador</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Mantengo tu lógica original
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