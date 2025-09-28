<?php
// ===== Flash stack: muestra y limpia mensajes de sesión =====

// Legacy (compatibilidad hacia atrás)
$legacy_ok   = $_SESSION['dep_creado']      ?? null;
$legacy_err1 = $_SESSION['error_creacion']  ?? null;
$legacy_err2 = $_SESSION['error_guardado']  ?? null;

// Nuevas claves recomendadas
$flash_ok   = $_SESSION['flash_success'] ?? null;
$flash_err  = $_SESSION['flash_error']   ?? null;
$flash_info = $_SESSION['flash_info']    ?? null;

// Componer mensajes finales (prioridad: nuevas, luego legacy)
$msg_ok   = $flash_ok  ?: $legacy_ok;
$msg_err  = $flash_err ?: ($legacy_err1 ?: $legacy_err2);
$msg_info = $flash_info ?: null;

// Limpiar llaves de sesión
unset($_SESSION['dep_creado'], $_SESSION['error_creacion'], $_SESSION['error_guardado'],
      $_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);

// Nada que mostrar
if (!$msg_ok && !$msg_err && !$msg_info) return;
?>

<!-- Pila visible con Bootstrap alerts -->
<div class="flash-stack">
  <?php if ($msg_ok): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>✔ Éxito:</strong> <?= htmlspecialchars($msg_ok) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <?php if ($msg_err): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>✖ Error:</strong> <?= htmlspecialchars($msg_err) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <?php if ($msg_info): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <strong>ℹ Info:</strong> <?= htmlspecialchars($msg_info) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>
</div>

<script>
(function(){
  // Auto-cerrar Bootstrap alerts después de 5s
  setTimeout(() => {
    document.querySelectorAll('.flash-stack .alert.show').forEach(a=>{
      try { new bootstrap.Alert(a).close(); } catch(e){}
    });
  }, 5000);

  // Lanzar también un toast con SweetAlert cuando todo cargue
  const toastData = {
    ok:  <?= json_encode((string)$msg_ok)  ?>,
    err: <?= json_encode((string)$msg_err) ?>,
    info:<?= json_encode((string)$msg_info) ?>
  };

  function launchSwalToast(){
    if (typeof Swal === 'undefined') return; // SweetAlert2 aún no disponible
    let icon = 'success', title = toastData.ok;
    if (!title && toastData.err){ icon = 'error'; title = toastData.err; }
    if (!title && toastData.info){ icon = 'info'; title = toastData.info; }
    if (!title) return;

    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 2200,
      timerProgressBar: true
    });
  }

  // Cuando termine de cargar el documento y scripts (footer)
  if (document.readyState === 'complete') {
    launchSwalToast();
  } else {
    window.addEventListener('load', launchSwalToast);
  }
})();
</script>

<style>
/* Fallback por si estilos.css no cargó aún */
.flash-stack{
  position: fixed; top: 64px; left: 0; right: 0;
  z-index: 2000; display: grid; gap: .5rem; justify-content: center;
  pointer-events: none;
}
.flash-stack .alert{
  pointer-events: auto;
  min-width: min(90vw, 700px);
  border-radius: 12px;
  box-shadow: 0 10px 24px rgba(0,0,0,.18);
}
</style>
