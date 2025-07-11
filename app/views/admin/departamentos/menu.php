<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Gestión de Departamentos y Sedes - Nexus RH";
include_once('../../shared/header.php');
?>

<div class="container mt-5">
  <div class="text-center mb-5">
    <h1 class="fw-bold text-primary">Gestión de Departamentos y Sedes</h1>
    <p class="lead text-muted">Administra las estructuras organizacionales de tu empresa de forma eficiente</p>
  </div>

  <div class="row g-4 justify-content-center">

    <!-- Tarjeta de Departamentos -->
    <div class="col-md-5">
      <div class="card border-primary shadow h-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <h4 class="card-title text-primary fw-bold">📁 Departamentos</h4>
            <p class="card-text">Crea, edita o elimina departamentos de la empresa. Asigna responsables y controla su información.</p>
          </div>
          <div class="mt-3">
            <a href="crear_dep.php" class="btn btn-outline-primary w-100 mb-2">➕ Nuevo Departamento</a>
            <a href="lista_dep.php" class="btn btn-primary w-100">📄 Ver Departamentos</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjeta de Sedes -->
    <div class="col-md-5">
      <div class="card border-success shadow h-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <h4 class="card-title text-success fw-bold">🏢 Sedes</h4>
            <p class="card-text">Registra nuevas ubicaciones, verifica su existencia y administra todos los datos de contacto y localización.</p>
          </div>
          <div class="mt-3">
            <a href="crear_sede.php" class="btn btn-outline-success w-100 mb-2">➕ Nueva Sede</a>
            <a href="lista_sedes.php" class="btn btn-success w-100">📄 Ver Sedes</a>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="mt-5 text-center">
    <a href="../../admin/index.php" class="btn btn-outline-dark">⬅ Volver al panel principal</a>
  </div>
</div>
