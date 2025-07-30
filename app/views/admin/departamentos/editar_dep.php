<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

$departamento = new Departamento();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['mensaje_error'] = "ID inválido.";
    header("Location: lista_dep.php");
    exit;
}

$dep = $departamento->obtenerPorId($id);
$usuarios = $departamento->obtenerUsuariosActivos();
$sedes = $departamento->listarSedesActivas();

$titulo_pagina = "Editar Departamento";
include_once('../../shared/header.php');
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <h2 class="text-primary mb-4">✏️ Editar Departamento</h2>

    <form id="formEditarDep" method="POST" action="actualiza_dep.php" autocomplete="off">
      <input type="hidden" name="id" value="<?= $dep['id'] ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre del Departamento</label>
          <input type="text" name="nombre" class="form-control text-uppercase" id="nombreDep"
                 value="<?= htmlspecialchars($dep['nombre']) ?>" required>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Sede</label>
          <select name="sede_id" class="form-select" id="sedeDep" required>
            <option value="">Seleccione una sede</option>
            <?php foreach ($sedes as $sede): ?>
              <option value="<?= $sede['id'] ?>" <?= $sede['id'] == $dep['sede_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($sede['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" rows="3" class="form-control text-uppercase" required><?= htmlspecialchars($dep['descripcion']) ?></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">Responsable</label>
          <select name="responsable_id" class="form-select">
            <option value="">-- Ninguno --</option>
            <?php foreach ($usuarios as $usuario): ?>
              <option value="<?= $usuario['id'] ?>" <?= $dep['responsable_id'] == $usuario['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($usuario['nombre_completo']) ?> (<?= $usuario['numero_empleado'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="lista_dep.php" class="btn btn-outline-secondary">← Cancelar</a>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => {
      input.value = input.value.toLocaleUpperCase('es-MX');
    });
  });

  const nombreInput = document.getElementById("nombreDep");
  const sedeSelect = document.getElementById("sedeDep");
  const mensajeNombre = document.getElementById("mensajeNombre");

  function validarNombreEditando() {
    const nombre = nombreInput.value.trim().toUpperCase();
    const sedeId = sedeSelect.value;
    const id = <?= $dep['id'] ?>;

    if (nombre === '' || sedeId === '') {
      mensajeNombre.textContent = '';
      return;
    }

    fetch(`actualiza_dep.php?verificar_nombre=${encodeURIComponent(nombre)}&sede_id=${sedeId}&id=${id}`)
      .then(res => res.json())
      .then(data => {
        mensajeNombre.textContent = data.existe 
          ? '❌ Ya existe un departamento con ese nombre en esta sede.'
          : '';
      })
      .catch(() => mensajeNombre.textContent = '⚠ Error al verificar el nombre.');
  }

  nombreInput.addEventListener('blur', validarNombreEditando);
  sedeSelect.addEventListener('change', validarNombreEditando);
</script>
