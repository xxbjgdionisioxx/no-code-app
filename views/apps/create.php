<!-- Create / Edit App -->
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-grid me-2"></i><?= isset($app) ? 'Edit App' : 'Create New App' ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST"
              action="<?= isset($app) ? APP_URL.'/apps/'.$app['id'] : APP_URL.'/apps' ?>"
              class="needs-validation" novalidate>
            <?= \Core\CSRF::field() ?>

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">App Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="name" name="name"
                       value="<?= htmlspecialchars($app['name'] ?? '') ?>"
                       placeholder="e.g., Inventory System, HR Platform" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                          placeholder="Describe what this app does..."><?= htmlspecialchars($app['description'] ?? '') ?></textarea>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Icon</label>
                    <div class="icon-picker" id="iconPicker">
                        <?php
                        $icons = ['bi-grid','bi-people','bi-box','bi-calendar','bi-clipboard','bi-briefcase',
                                  'bi-bar-chart','bi-gear','bi-house','bi-truck','bi-cart','bi-star',
                                  'bi-flag','bi-building','bi-bank','bi-cup','bi-heart','bi-lightning'];
                        $selectedIcon = $app['icon'] ?? 'bi-grid';
                        foreach ($icons as $ic): ?>
                        <button type="button" class="icon-option <?= $ic === $selectedIcon ? 'active' : '' ?>"
                                data-icon="<?= $ic ?>" title="<?= $ic ?>">
                            <i class="bi <?= $ic ?>"></i>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="icon" id="selectedIcon" value="<?= htmlspecialchars($selectedIcon) ?>">
                </div>
                <div class="col-md-6">
                    <label for="color" class="form-label fw-semibold">Brand Color</label>
                    <div class="color-presets mb-2" id="colorPresets">
                        <?php
                        $colors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#eab308',
                                   '#22c55e','#14b8a6','#06b6d4','#3b82f6'];
                        $selectedColor = $app['color'] ?? '#6366f1';
                        foreach ($colors as $c): ?>
                        <button type="button" class="color-dot <?= $c === $selectedColor ? 'active' : '' ?>"
                                data-color="<?= $c ?>" style="background:<?= $c ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="color" class="form-control form-control-color" id="color" name="color"
                           value="<?= htmlspecialchars($selectedColor) ?>" title="Choose brand color">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> <?= isset($app) ? 'Save Changes' : 'Create App' ?>
                </button>
                <a href="<?= APP_URL ?>/apps" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
// Icon picker
document.querySelectorAll('.icon-option').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.icon-option').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('selectedIcon').value = btn.dataset.icon;
    });
});
// Color presets
document.querySelectorAll('.color-dot').forEach(dot => {
    dot.addEventListener('click', () => {
        document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
        dot.classList.add('active');
        document.getElementById('color').value = dot.dataset.color;
    });
});
document.getElementById('color').addEventListener('input', e => {
    document.getElementById('selectedColor')?.setAttribute('value', e.target.value);
});
</script>
