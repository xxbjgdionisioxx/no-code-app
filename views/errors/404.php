<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head><meta charset="UTF-8"><title>404 Not Found — AppForge</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
<style>body{font-family:'Inter',sans-serif;background:#0f1117;}</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 text-center">
<div>
    <div style="font-size:5rem;line-height:1;" class="mb-4">🔍</div>
    <h1 class="display-4 fw-bold text-white">404</h1>
    <p class="lead text-muted mb-4">The page you're looking for doesn't exist.</p>
    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/apps" class="btn btn-primary">
        <i class="bi bi-house me-1"></i> Back to Apps
    </a>
</div>
</body>
</html>
