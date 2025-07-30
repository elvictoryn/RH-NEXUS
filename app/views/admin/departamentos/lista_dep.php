<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

$titulo_pagina = "Lista de Departamentos";
include_once('../../shared/header.php');

$departamento = new Departamento();
$departamentos = $departamento->obtenerTodosConSedeYResponsable();

$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
$mensaje_error = $_SESSION['mensaje_error'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de Departamentos</h2>
      <a href="crear_dep.php" class="btn btn-success">+ Registrar Nuevo Departamento</a>
      <a href="menu.php" class="btn btn-success">← Regresar</a>
    </div>
    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>

    <?php if (empty($departamentos)): ?>
      <div class="alert alert-info text-center">No hay departamentos registrados.</div>
    <?php else: ?>
      <div class="mb-3">
        <input type="text" id="busqueda" class="form-control" placeholder="🔍 Buscar por nombre, sede o responsable...">
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="tablaDepartamentos">
          <thead class="table-secondary text-center">
  <tr>
    <th data-columna="0">#</th>
    <th data-columna="1" style="cursor: pointer;">Nombre 🔽</th>
    <th data-columna="2" style="cursor: pointer;">Sede 🔽</th>
    <th data-columna="3" style="cursor: pointer;">Responsable 🔽</th>
    <th>Acciones</th>
  </tr>
</thead>

          <tbody class="text-center">
            <?php foreach ($departamentos as $index => $dep): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($dep['nombre']) ?></td>
                <td><?= htmlspecialchars($dep['sede_nombre']) ?></td>
                <td>
                  <?= $dep['responsable'] 
                    ? htmlspecialchars($dep['responsable']) 
                    : '<span class="text-muted">No asignado</span>' ?>
                </td>
                <td>
                  <a href="editar_dep.php?id=<?= $dep['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ </a>
                  <a href="eliminar_dep.php?id=<?= $dep['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmarEliminacion()">🗑️</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  function confirmarEliminacion() {
    return confirm("¿Estás seguro de que deseas eliminar este departamento?");
  }

  // 🔍 Filtro en tiempo real
  document.getElementById('busqueda')?.addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaDepartamentos tbody tr");

    filas.forEach(fila => {
      const texto = fila.textContent.toLowerCase();
      fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
  });

  // 🔄 Ordenar columnas con flechas
  document.querySelectorAll("#tablaDepartamentos th[data-columna]").forEach(th => {
    let ascendente = true;

    th.addEventListener('click', () => {
      const columnaIndex = parseInt(th.dataset.columna);
      const filas = Array.from(document.querySelectorAll("#tablaDepartamentos tbody tr"));

      filas.sort((a, b) => {
        const aText = a.children[columnaIndex].textContent.trim().toLowerCase();
        const bText = b.children[columnaIndex].textContent.trim().toLowerCase();
        return ascendente
          ? aText.localeCompare(bText, 'es', { sensitivity: 'base' })
          : bText.localeCompare(aText, 'es', { sensitivity: 'base' });
      });

      ascendente = !ascendente;
      const tbody = document.querySelector("#tablaDepartamentos tbody");
      filas.forEach(fila => tbody.appendChild(fila));

      document.querySelectorAll("#tablaDepartamentos th[data-columna]").forEach(otherTh => {
        if (otherTh !== th) {
          otherTh.textContent = otherTh.textContent.replace(' ⬆', '').replace(' ⬇', '');
        }
      });

      th.textContent = th.textContent.replace(' ⬆', '').replace(' ⬇', '');
      th.textContent += ascendente ? ' ⬇' : ' ⬆';
    });
  });

  setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
    document.querySelector('.alert-danger')?.remove();
  }, 4000);
</script>
