<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/auth.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DGC PRMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css?v=<?= time() ?>">
  <link rel="stylesheet" href="/assets/css/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="/assets/css/tables.css?v=<?= time() ?>">
</head>

<body data-theme="dark" class="bg-light">

<!-- Mobile sidebar toggle -->
<div class="d-md-none bg-dark text-white p-2 d-flex align-items-center justify-content-between">
  <a href="/dashboard/index.php" class="text-white text-decoration-none d-flex align-items-center gap-2">
    <img src="/logo/cropped-Logo.png" alt="Logo" style="height:28px; filter: brightness(0) invert(1);">
    <span class="fw-semibold">DGC PRMS</span>
  </a>
  <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
    <i class="bi bi-list"></i> Menu
  </button>
</div>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav id="sidebarMenu"
         class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse">
      <?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/sidebar.php'; ?>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 pt-4">

<?php
$__flash_msg   = $_SESSION['popup_error']  ?? $_SESSION['popup_success'] ?? null;
$__flash_isErr = isset($_SESSION['popup_error']);
if ($__flash_msg !== null) {
    unset($_SESSION['popup_error'], $_SESSION['popup_success']);
}
?>

<?php if ($__flash_msg !== null): ?>
  <!-- Global Flash Modal -->
  <div class="modal fade" id="flashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header <?= $__flash_isErr ? 'bg-danger' : 'bg-success' ?> text-white">
          <h5 class="modal-title">
            <?= $__flash_isErr ? 'Action Blocked' : 'Success' ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?= htmlspecialchars($__flash_msg) ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn <?= $__flash_isErr ? 'btn-danger' : 'btn-success' ?>" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('flashModal');
    if (el && window.bootstrap && bootstrap.Modal) {
      new bootstrap.Modal(el).show();
    }
  });
  </script>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="container mt-3">
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php unset($_SESSION['error']); endif; ?>
