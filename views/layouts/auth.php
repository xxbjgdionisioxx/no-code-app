<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Modulyn', ENT_QUOTES) ?> — Modulyn</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
</head>
<body class="auth-body d-flex align-items-center justify-content-center min-vh-100">

    <div class="auth-card">
        <!-- Logo -->
        <div class="text-center mb-4">
            <div class="auth-logo mb-3">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </div>
            <h1 class="h4 fw-bold text-white mb-1">Modulyn</h1>
            <p class="text-muted small">No-Code Application Builder</p>
        </div>

        <!-- Flash -->
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>

        <!-- View Content -->
        <?= $content ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
