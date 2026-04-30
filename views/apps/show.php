<!-- App Detail / Overview -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="app-big-icon" style="background: <?= htmlspecialchars($app['color']) ?>">
        <i class="bi <?= htmlspecialchars($app['icon']) ?>"></i>
    </div>
    <div>
        <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($app['name']) ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($app['description'] ?? '') ?></p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <?php if (!empty($user['is_admin'])): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Module
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['module_count'] ?></div>
            <div class="stat-label">Modules</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['record_count']) ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
</div>

<!-- Modules Grid -->
<h2 class="h5 fw-semibold mb-3">Modules</h2>

<?php if (empty($modules)): ?>
<div class="empty-state text-center py-4">
    <div class="empty-icon mb-2"><i class="bi bi-table"></i></div>
    <p class="text-muted mb-3">No modules yet. Add your first module to define your data structure.</p>
    <?php if (!empty($user['is_admin'])): ?>
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Module
    </a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($modules as $m): ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="module-card card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="module-icon">
                        <i class="bi <?= htmlspecialchars($m['icon']) ?>"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($m['name']) ?></h6>
                        <span class="text-muted small">/<?= htmlspecialchars($m['slug']) ?></span>
                    </div>
                </div>
                <?php if ($m['description']): ?>
                <p class="text-muted small mb-3"><?= htmlspecialchars($m['description']) ?></p>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $m['slug'] ?>" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-table me-1"></i> Records
                    </a>
                    <?php if (!empty($user['is_admin'])): ?>
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $m['id'] ?>/builder"
                       class="btn btn-sm btn-outline-secondary" title="Open Builder">
                        <i class="bi bi-tools"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
