<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Builder', ENT_QUOTES) ?> — Modulyn Builder</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/builder.css">
    <meta name="csrf-token" content="<?= \Core\Session::get('_csrf_token') ?>">
</head>
<body class="builder-body">

<!-- Builder Topbar -->
<nav class="builder-topbar navbar px-3">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?? '' ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to App
        </a>
        <div class="vr"></div>
        <span class="fw-semibold text-white">
            <i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i> Modulyn Builder
        </span>
        <?php if (!empty($app)): ?>
            <span class="badge bg-primary"><?= htmlspecialchars($app['name']) ?></span>
        <?php endif; ?>
        <?php if (!empty($module)): ?>
            <span class="text-muted">/</span>
            <span class="text-white"><?= htmlspecialchars($module['name']) ?></span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($module)): ?>
            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>" class="btn btn-sm btn-success" target="_blank">
                <i class="bi bi-eye"></i> Preview Live
            </a>
        <?php endif; ?>
        <?php
        $u = \Core\Session::get('user');
        if ($u):
        ?>
        <span class="text-muted small"><?= htmlspecialchars($u['name']) ?></span>
        <?php endif; ?>
    </div>
</nav>

<div class="builder-layout">
    <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/js/builder.js"></script>
<script src="<?= APP_URL ?>/public/js/form_preview.js"></script>
</body>
</html>
