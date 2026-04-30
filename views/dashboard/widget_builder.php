<!-- Widget Builder Form -->
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h4 fw-bold mb-0">Add Dashboard Widget</h1>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets" id="widgetForm">
            <?= \Core\CSRF::field() ?>

            <div class="mb-4">
                <label for="title" class="form-label fw-semibold">Widget Title</label>
                <input type="text" class="form-control" id="title" name="title"
                       placeholder="e.g., Total Products, Revenue by Category" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Widget Type</label>
                <div class="widget-type-grid" id="widgetTypeGrid">
                    <?php
                    $widgetTypes = [
                        'count'     => ['icon' => 'bi-hash',         'label' => 'Count',     'desc' => 'Number of records'],
                        'sum'       => ['icon' => 'bi-calculator',   'label' => 'Sum',       'desc' => 'Sum of a numeric field'],
                        'average'   => ['icon' => 'bi-graph-up',     'label' => 'Average',   'desc' => 'Average of a numeric field'],
                        'bar_chart' => ['icon' => 'bi-bar-chart',    'label' => 'Bar Chart', 'desc' => 'Group by a dropdown field'],
                        'pie_chart' => ['icon' => 'bi-pie-chart',    'label' => 'Pie / Donut','desc' => 'Distribution chart'],
                    ];
                    foreach ($widgetTypes as $wt => $info): ?>
                    <label class="widget-type-option" for="wt_<?= $wt ?>">
                        <input type="radio" name="widget_type" id="wt_<?= $wt ?>" value="<?= $wt ?>"
                               class="widget-type-radio" <?= $wt === 'count' ? 'checked' : '' ?>>
                        <i class="bi <?= $info['icon'] ?> fs-4 mb-1"></i>
                        <span class="fw-semibold"><?= $info['label'] ?></span>
                        <span class="small text-muted"><?= $info['desc'] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="widget_type" id="selectedWidgetType" value="count">
            </div>

            <div class="mb-4">
                <label for="module_id" class="form-label fw-semibold">Module</label>
                <select class="form-select" id="module_id" name="module_id" required>
                    <option value="">— Select Module —</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 d-none" id="fieldGroup">
                <label for="field_id" class="form-label fw-semibold">Field <span class="text-muted small">(for sum/avg/chart)</span></label>
                <select class="form-select" id="field_id" name="field_id">
                    <option value="">— Select Field —</option>
                    <?php foreach ($modules as $m): ?>
                        <?php if (!empty($fieldsByModule[$m['id']])): ?>
                        <optgroup label="<?= htmlspecialchars($m['name']) ?>" data-module-id="<?= $m['id'] ?>">
                            <?php foreach ($fieldsByModule[$m['id']] as $f): ?>
                            <option value="<?= $f['id'] ?>" data-module="<?= $m['id'] ?>">
                                <?= htmlspecialchars($f['name']) ?> (<?= $f['field_type'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="chart_color" class="form-label fw-semibold">Color</label>
                    <div class="color-presets mb-2">
                        <?php
                        $colors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#22c55e','#14b8a6','#3b82f6'];
                        foreach ($colors as $c): ?>
                        <button type="button" class="color-dot" data-color="<?= $c ?>"
                                style="background:<?= $c ?>" onclick="document.getElementById('chart_color').value='<?= $c ?>'"></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="color" class="form-control form-control-color" id="chart_color"
                           name="chart_color" value="#6366f1">
                </div>
                <div class="col-md-6">
                    <label for="width" class="form-label fw-semibold">Width (Bootstrap Columns)</label>
                    <select class="form-select" id="width" name="width">
                        <option value="3">3 — Quarter (4 per row)</option>
                        <option value="4" selected>4 — Third (3 per row)</option>
                        <option value="6">6 — Half (2 per row)</option>
                        <option value="12">12 — Full width</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-plus-lg me-1"></i> Add Widget
                </button>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>

<script>
// Widget type toggle cards
document.querySelectorAll('.widget-type-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.widget-type-option').forEach(o => o.classList.remove('active'));
        this.closest('.widget-type-option').classList.add('active');
        document.getElementById('selectedWidgetType').value = this.value;

        // Show field selector for non-count types
        const needsField = ['sum', 'average', 'bar_chart', 'pie_chart'].includes(this.value);
        document.getElementById('fieldGroup').classList.toggle('d-none', !needsField);
    });
});

// Filter field options when module changes
document.getElementById('module_id').addEventListener('change', function () {
    const mid = this.value;
    document.querySelectorAll('#field_id optgroup').forEach(og => {
        og.style.display = og.dataset.moduleId === mid ? '' : 'none';
    });
    // Reset field selection
    document.getElementById('field_id').value = '';
});

// Activate first radio visually
document.querySelector('.widget-type-option')?.classList.add('active');
</script>
