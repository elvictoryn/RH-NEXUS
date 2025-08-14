<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Crear Usuario";
include_once('../../shared/header.php');
require_once('../../../models/Usuario.php');
require_once('../../../models/Departamento.php');

$usuarioModel = new Usuario();
$departamentoModel = new Departamento();

$sedes = $departamentoModel->obtenerSedes();
$departamentos = $departamentoModel->obtenerTodosActivos();
?>

<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-body">
            <h3 class="mb-4 text-center">➕ Crear Usuario</h3>
            <?php if (isset($_SESSION['usuario_guardado'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['usuario_guardado'] ?>
                    <?php unset($_SESSION['usuario_guardado']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

            <?php if (isset($_SESSION['error_guardado'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_guardado'] ?>
                    <?php unset($_SESSION['error_guardado']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="formCrearUsuario" method="POST" action="guardar_usuario.php" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" class="form-control text-uppercase" name="nombre_completo" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control text-uppercase" id="usuario" name="usuario" required>
                        <div id="usuario-feedback" class="form-text text-danger d-none">Este usuario ya existe.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="contrasena" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="rol" id="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin">Administrador</option>
                            <option value="rh">RH</option>
                            <option value="jefe_area">Jefe de área</option>
                            <option value="gerente">Gerente</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Número de empleado</label>
                        <input type="text" class="form-control text-uppercase" id="numero_empleado" name="numero_empleado" required>
                        <div id="empleado-feedback" class="form-text text-danger d-none">Este número de empleado ya existe.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" required>
                    </div>
                    <div class="col-md-6 d-none" id="grupo_sede">
                        <label class="form-label">Sede</label>
                        <select class="form-select" name="sede_id" id="sede_id">
                            <option value="">Seleccione una sede</option>
                            <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>"><?= $sede['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="gerente-feedback" class="form-text text-danger d-none">Ya existe un gerente para esta sede.</div>
                    </div>
                    <div class="col-md-6 d-none" id="grupo_departamento">
                        <label class="form-label">Departamento</label>
                        <select class="form-select" name="departamento_id" id="departamento_id">
                            <option value="">Seleccione un departamento</option>
                        </select>
                        <div id="jefe-feedback" class="form-text text-danger d-none">Ya existe un jefe asignado a este departamento.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fotografía</label>
                        <input type="file" class="form-control" name="fotografia" accept="image/*">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-between">
                     <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
                     <a href="lista_usuario.php" class="btn btn-outline-info">📋 Lista de Usuarios</a>
                    <button type="submit" class="btn btn-success" id="btnGuardar">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => input.value = input.value.toUpperCase());
});

const rol = document.getElementById('rol');
const grupoSede = document.getElementById('grupo_sede');
const grupoDepartamento = document.getElementById('grupo_departamento');
const sedeSelect = document.getElementById('sede_id');
const depSelect = document.getElementById('departamento_id');
const btnGuardar = document.getElementById('btnGuardar');

// Mostrar/ocultar campos según el rol
rol.addEventListener('change', () => {
    grupoSede.classList.add('d-none');
    grupoDepartamento.classList.add('d-none');
    document.getElementById('gerente-feedback').classList.add('d-none');
    document.getElementById('jefe-feedback').classList.add('d-none');
    sedeSelect.value = "";
    depSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
    btnGuardar.disabled = false;

    if (['rh', 'jefe_area', 'gerente'].includes(rol.value)) grupoSede.classList.remove('d-none');
    if (['rh', 'jefe_area'].includes(rol.value)) grupoDepartamento.classList.remove('d-none');
});

// Validar usuario único
document.getElementById('usuario').addEventListener('blur', async () => {
    const usuario = document.getElementById('usuario').value.trim();
    const feedback = document.getElementById('usuario-feedback');
    if (!usuario) return;

    const res = await fetch(`../../../controllers/usuariosController.php?action=verificar_usuario&usuario=${usuario}`);
    const data = await res.json();

    feedback.classList.toggle('d-none', !data.existe);
    btnGuardar.disabled = data.existe;
    ocultarFeedback(feedback);
});

// Validar número de empleado único
document.getElementById('numero_empleado').addEventListener('blur', async () => {
    const numero = document.getElementById('numero_empleado').value.trim();
    const feedback = document.getElementById('empleado-feedback');
    if (!numero) return;

    const res = await fetch(`../../../controllers/usuariosController.php?action=verificar_num_empleado&numero=${numero}`);
    const data = await res.json();

    feedback.classList.toggle('d-none', !data.existe);
    btnGuardar.disabled = data.existe;
    ocultarFeedback(feedback);
});

// Cargar departamentos al elegir sede
sedeSelect.addEventListener('change', async () => {
    const sede_id = sedeSelect.value;
    depSelect.innerHTML = '<option value="">Cargando...</option>';

    const res = await fetch(`../../../controllers/usuariosController.php?action=departamentos_por_sede&id=${sede_id}`);
    const data = await res.json();

    depSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
    data.forEach(dep => {
        depSelect.innerHTML += `<option value="${dep.id}" data-responsable="${dep.responsable_id}">${dep.nombre}</option>`;
    });

    // Validación para gerente (en tiempo real al cambiar sede)
    if (rol.value === 'gerente') {
        validarGerente();
    }

    // Validación para jefe de área si ya hay un departamento seleccionado
    if (rol.value === 'jefe_area' && depSelect.value) {
        validarJefe();
    }
});

// Validar jefe al cambiar departamento
depSelect.addEventListener('change', () => {
    if (rol.value === 'jefe_area') {
        validarJefe();
    }
});

// También validar jefe si cambia sede y ya hay un departamento
sedeSelect.addEventListener('change', () => {
    if (rol.value === 'jefe_area' && depSelect.value) {
        validarJefe();
    }
});

// Validar gerente automáticamente
async function validarGerente() {
    const sede_id = sedeSelect.value;
    const feedback = document.getElementById('gerente-feedback');
    if (!sede_id) return;

    const res = await fetch(`../../../controllers/usuariosController.php?action=verificar_gerente&sede_id=${sede_id}`);
    const data = await res.json();

    feedback.classList.toggle('d-none', !data.existe);
    btnGuardar.disabled = data.existe;
    ocultarFeedback(feedback);
}

// Validar jefe automáticamente
async function validarJefe() {
    const sede_id = sedeSelect.value;
    const departamento_id = depSelect.value;
    const feedback = document.getElementById('jefe-feedback');
    if (!sede_id || !departamento_id) return;

    const res = await fetch(`../../../controllers/usuariosController.php?action=verificar_jefe&sede_id=${sede_id}&departamento_id=${departamento_id}`);
    const data = await res.json();

    feedback.classList.toggle('d-none', !data.existe);
    btnGuardar.disabled = data.existe;
    ocultarFeedback(feedback);
}

// Oculta un mensaje después de 10 segundos
function ocultarFeedback(feedbackElement) {
    setTimeout(() => {
        feedbackElement.classList.add('d-none');
    }, 20000);
}
</script>
