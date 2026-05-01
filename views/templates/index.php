<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="display-5 fw-bold mb-2">Template Gallery</h1>
            <p class="text-muted fs-5 mb-0">Launch your next project instantly with pre-built system templates.</p>
        </div>
        <a href="<?= APP_URL ?>/apps" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to My Apps
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($templates as $t): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm template-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="template-icon" style="background: <?= $t['color'] ?>20; color: <?= $t['color'] ?>;">
                            <i class="bi <?= $t['icon'] ?> fs-3"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-0"><?= htmlspecialchars($t['name']) ?></h3>
                    </div>
                    
                    <p class="text-muted small mb-4" style="min-height: 3rem;">
                        <?= htmlspecialchars($t['description']) ?>
                    </p>

                    <form action="<?= APP_URL ?>/templates/<?= $t['id'] ?>/install" method="POST">
                        <?= \Core\CSRF::field() ?>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            Use this Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.template-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid var(--border-color) !important;
}
.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
    border-color: var(--accent) !important;
}
.template-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
