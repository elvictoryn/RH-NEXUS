<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Detalles del Usuario";
include_once('../../shared/header.php');
require_once('../../../models/Usuario.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger m-4'>ID inválido</div>";
    exit;
}

$id = $_GET['id'];
$modelo = new Usuario();
$usuario = $modelo->obtenerPorId($id);

if (!$usuario) {
    echo "<div class='alert alert-warning m-4'>Usuario no encontrado</div>";
    exit;
}
?>

<style>
  .modern-card {
    background: rgba(224, 191, 191, 0.95);
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    padding: 2rem;
    max-width: 900px;
    margin: auto;
    color: #333;
  }

  .modern-title {
    font-weight: 600;
    font-size: 2rem;
    color: #1e3a8a;
    margin-bottom: 1.5rem;
  }

  .modern-avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #1e3a8a;
    margin-bottom: 1rem;
  }

  .modern-info-title {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
  }

  .modern-info-value {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 1rem;
  }

  .modern-btns {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
  }

  .modern-btns a {
    padding: 0.6rem 1.4rem;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
  }

  .modern-btns .btn-volver {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ccc;
  }

  .modern-btns .btn-editar {
    background-color: #ffc107;
    color: #212529;
  }

  @media (max-width: 768px) {
    .modern-btns {
      flex-direction: column;
      gap: 0.8rem;
    }
  }
</style>

<div class="container mt-5">
  <div class="modern-card">

    <div class="text-center">
      <h2 class="modern-title">Perfil de Usuario</h2>

      <?php if (!empty($usuario['fotografia'])): ?>
        <img src="../../../public/uploads/<?= $usuario['fotografia'] ?>" alt="Avatar" class="modern-avatar">
      <?php else: ?>
        <div class="text-muted mb-3">Sin fotografía disponible</div>
      <?php endif; ?>
    </div>

    <div class="row mt-4">
      <div class="col-md-6">
        <div class="modern-info-title">Nombre completo</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['nombre_completo']) ?></div>

        <div class="modern-info-title">Usuario</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['usuario']) ?></div>

        <div class="modern-info-title">Rol</div>
        <div class="modern-info-value"><?= ucfirst($usuario['rol']) ?></div>

        <div class="modern-info-title">Correo</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['correo']) ?></div>
      </div>

      <div class="col-md-6">
        <div class="modern-info-title">Departamento</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['departamento']) ?></div>

        <div class="modern-info-title">Sede</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['sede']) ?></div>

        <div class="modern-info-title">Número de empleado</div>
        <div class="modern-info-value"><?= htmlspecialchars($usuario['numero_empleado']) ?></div>

        <div class="modern-info-title">Fecha de registro</div>
        <div class="modern-info-value"><?= $usuario['fecha_registro'] ?></div>
      </div>
    </div>

    <div class="modern-btns">
      <a href="lista.php" class="btn-volver">← Volver</a>
      <a href="editar.php?id=<?= $usuario['id'] ?>" class="btn-editar">✏️ Editar Usuario</a>
    </div>
  </div>
</div>
