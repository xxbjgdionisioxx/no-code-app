<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AppForge', ENT_QUOTES) ?> — AppForge</title>
    <meta name="description" content="AppForge No-Code Application Builder Platform">
    <meta name="csrf-token" content="<?= \Core\Session::get('_csrf_token') ?>">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
</head>
<body class="app-body">

<!-- Top Navbar -->
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>

<div class="d-flex" style="min-height: calc(100vh - 56px);">

    <!-- Sidebar -->
    <?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content flex-grow-1 p-4">
        <!-- Flash Messages -->
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>

        <!-- Page Content -->
        <?= $content ?>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
