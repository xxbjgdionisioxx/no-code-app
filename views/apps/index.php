<!-- Apps Index — My Apps Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">My Apps</h1>
        <p class="text-muted mb-0">Build and manage your no-code applications</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/templates" class="btn btn-outline-accent" style="color: var(--accent); border-color: var(--accent);">
            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Explore Templates
        </a>
        <a href="<?= APP_URL ?>/apps/create" class="btn btn-primary" id="create-app-btn">
            <i class="bi bi-plus-lg me-1"></i> New App
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="mb-4">
    <div class="input-group input-group-sm" style="max-width:300px;">
        <span class="input-group-text bg-transparent border-secondary text-muted">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" class="form-control" id="app-search" placeholder="Search apps...">
    </div>
</div>

<?php if (empty($apps)): ?>
<div class="empty-state text-center py-5">
    <div class="empty-icon mb-3"><i class="bi bi-grid-3x3-gap"></i></div>
    <h2 class="h5 text-white mb-2">No apps yet</h2>
    <p class="text-muted mb-4">Create your first no-code application to get started.</p>
    <a href="<?= APP_URL ?>/apps/create" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg me-1"></i> Create Your First App
    </a>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($apps as $a): ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="app-card card h-100">
            <div class="app-card-header" style="background: linear-gradient(135deg, <?= htmlspecialchars($a['color']) ?>22, <?= htmlspecialchars($a['color']) ?>44);">
                <div class="app-card-icon" style="background: <?= htmlspecialchars($a['color']) ?>">
                    <i class="bi <?= htmlspecialchars($a['icon']) ?>"></i>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($a['name']) ?></h5>
                <?php if ($a['description']): ?>
                <p class="card-text text-muted small mb-3"><?= htmlspecialchars(mb_substr($a['description'], 0, 80)) ?>...</p>
                <?php endif; ?>
                <div class="d-flex gap-3 text-muted small mb-3">
                    <span><i class="bi bi-calendar3 me-1"></i><?= date('M j, Y', strtotime($a['created_at'])) ?></span>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="<?= APP_URL ?>/apps/<?= $a['id'] ?>" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-arrow-right-circle me-1"></i> Open
                </a>
                <?php if (!empty($user['is_admin'])): ?>
                <a href="<?= APP_URL ?>/apps/<?= $a['id'] ?>/edit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= APP_URL ?>/apps/<?= $a['id'] ?>/delete" class="btn btn-outline-danger btn-sm" data-confirm="WARNING: This will delete the application, all its modules, and all its records! Are you completely sure?">
                    <i class="bi bi-trash"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
