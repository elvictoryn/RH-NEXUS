<?php 
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Crear Usuario"; // Cambia según la página
include_once('../../shared/header.php');
?>
<div class="d-flex gap-2">
  <a href="menu.php" class="btn btn-outline-light">← Regresar</a>
  <a href="lista.php" class="btn btn-success">Lista de usuarios</a>
</div>

<div class="container mt-4">
  <div class="card bg-dark text-white shadow rounded p-4">
    <h2 class="mb-4">Crear nuevo usuario</h2>
    

    <form id="formCrearUsuario" enctype="multipart/form-data">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Usuario</label>
          <input type="text" name="usuario" id="usuario" class="form-control form-control-sm" required>
          <small id="usuarioError" class="text-danger d-none">Este usuario ya existe</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control form-control-sm" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Rol</label>
          <select name="rol" class="form-select form-select-sm" required>
            <option value="">Seleccionar</option>
            <option value="admin">Administrador</option>
            <option value="rh">Recursos Humanos</option>
            <option value="jefe">Jefe de Área</option>
            <option value="gerente">Gerente</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nombre completo</label>
          <input type="text" name="nombre_completo" class="form-control form-control-sm text-uppercase" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Departamento</label>
          <input type="text" name="departamento" class="form-control form-control-sm text-uppercase" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Sede</label>
          <input type="text" name="sede" class="form-control form-control-sm text-uppercase" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Número de empleado</label>
          <input type="text" name="numero_empleado" id="numero_empleado" class="form-control form-control-sm" required>
          <small id="empleadoError" class="text-danger d-none">Este número de empleado ya existe</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo electrónico</label>
          <input type="email" name="correo" class="form-control form-control-sm" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Fotografía</label>
          <input type="file" name="fotografia" class="form-control form-control-sm" accept="image/*" required>
          <div class="mt-2">
            <img id="previewImg" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 120px; display:none;">
          </div>
        </div>

      </div>

      <div class="mt-4 text-end">
        <button type="submit" class="btn btn-success btn-sm">Crear usuario</button>
      </div>
    </form>
  </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="toastUsuario" class="toast align-items-center text-white border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastTexto"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
  // Validación usuario
  $('#usuario').on('input', function () {
    const usuario = $(this).val();
    if (usuario.length > 2) {
      $.post('../../../controllers/usuariosController.php', {
        validar_usuario: true,
        usuario: usuario,
        numero_empleado: ''
      }, function (res) {
        $('#usuarioError').toggleClass('d-none', res.trim() !== 'existe');
      });
    } else {
      $('#usuarioError').addClass('d-none');
    }
  });

  // Validación número de empleado
  $('#numero_empleado').on('input', function () {
    const numero = $(this).val();
    if (numero.length > 2) {
      $.post('../../../controllers/usuariosController.php', {
        validar_usuario: true,
        usuario: '',
        numero_empleado: numero
      }, function (res) {
        $('#empleadoError').toggleClass('d-none', res.trim() !== 'existe');
      });
    } else {
      $('#empleadoError').addClass('d-none');
    }
  });

  // Vista previa
  $('input[name="fotografia"]').on('change', function () {
    const archivo = this.files[0];
    if (archivo) {
      const lector = new FileReader();
      lector.onload = function(e) {
        $('#previewImg').attr('src', e.target.result).show();
      };
      lector.readAsDataURL(archivo);
    }
  });

  // Envío del formulario
  $('#formCrearUsuario').submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
      url: '../../../controllers/usuariosController.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function (res) {
        let mensaje = '';
        let clase = '';
        if (res.trim() === 'existe') {
          mensaje = 'El usuario o número de empleado ya existe.';
          clase = 'bg-danger';
        } else if (res.trim() === 'exito') {
          mensaje = 'Usuario creado con éxito.';
          clase = 'bg-success';
          $('#formCrearUsuario')[0].reset();
          $('#previewImg').hide();
          $('#usuarioError, #empleadoError').addClass('d-none');
        } else {
          mensaje = 'Hubo un error inesperado.';
          clase = 'bg-warning';
        }

        $('#toastTexto').text(mensaje);
        $('#toastUsuario').removeClass('bg-success bg-danger bg-warning').addClass(clase);
        new bootstrap.Toast(document.getElementById('toastUsuario')).show();
      }
    });
  });
});
</script>
