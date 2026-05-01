<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AppForge', ENT_QUOTES) ?> — AppForge</title>
    <meta name="description" content="AppForge No-Code Application Builder Platform">
    <meta name="csrf-token" content="<?= \Core\Session::get('_csrf_token') ?>">
    <meta name="app-url" content="<?= APP_URL ?>">

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

<!-- Page load progress bar -->
<div id="page-progress"></div>

<!-- Top Navbar -->
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>

<div class="d-flex" style="min-height: calc(100vh - 56px);">

    <!-- App Notifications Container (Floating) -->
    <div class="alert-container">
        <?php if ($flash = \Core\Session::get('flash')): \Core\Session::delete('flash'); ?>
            <div class="app-alert app-alert-<?= $flash['type'] ?>" role="alert" id="app-flash-alert">
                <div class="alert-icon">
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    <?php elseif ($flash['type'] === 'danger'): ?>
                        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                    <?php else: ?>
                        <i class="bi bi-info-circle-fill fs-4"></i>
                    <?php endif; ?>
                </div>
                <div class="alert-content">
                    <div class="alert-title"><?= ucfirst($flash['type'] === 'danger' ? 'error' : $flash['type']) ?></div>
                    <div class="alert-message"><?= htmlspecialchars($flash['message']) ?></div>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.classList.add('alert-fade-out'); setTimeout(() => this.parentElement.remove(), 300);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <script>
                // Auto-dismiss flash message after 5 seconds
                setTimeout(() => {
                    const alert = document.getElementById('app-flash-alert');
                    if (alert) {
                        alert.classList.add('alert-fade-out');
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            </script>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content flex-grow-1 p-4">
        <!-- Page Content -->
        <?= $content ?>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- App JS -->
    <!-- Custom Confirmation Modal -->
    <div id="app-confirm-modal" class="modal-overlay">
        <div class="app-modal">
            <div class="modal-icon-wrapper">
                <i class="bi bi-trash3-fill"></i>
            </div>
            <div class="modal-title">Are you sure?</div>
            <div class="modal-text" id="confirm-modal-text">This action is permanent and cannot be undone.</div>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" id="confirm-modal-cancel">Cancel</button>
                <a href="#" class="btn-modal-confirm" id="confirm-modal-proceed">Delete</a>
            </div>
        </div>
    </div>

    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
