<!-- Widget Builder Form -->
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h4 fw-bold mb-0"><?= isset($widget) ? 'Edit Dashboard Widget' : 'Add Dashboard Widget' ?></h1>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets<?= isset($widget) ? '/' . $widget['id'] : '' ?>" id="widgetForm">
            <?= \Core\CSRF::field() ?>

            <div class="mb-4">
                <label for="title" class="form-label fw-semibold">Widget Title</label>
                <input type="text" class="form-control" id="title" name="title"
                       value="<?= isset($widget) ? htmlspecialchars($widget['title']) : '' ?>"
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
                    $currentType = isset($widget) ? $widget['widget_type'] : 'count';
                    foreach ($widgetTypes as $wt => $info): ?>
                    <label class="widget-type-option <?= $wt === $currentType ? 'active' : '' ?>" for="wt_<?= $wt ?>">
                        <input type="radio" name="widget_type" id="wt_<?= $wt ?>" value="<?= $wt ?>"
                               class="widget-type-radio" <?= $wt === $currentType ? 'checked' : '' ?>>
                        <i class="bi <?= $info['icon'] ?> fs-4 mb-1"></i>
                        <span class="fw-semibold"><?= $info['label'] ?></span>
                        <span class="small text-muted"><?= $info['desc'] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="widget_type" id="selectedWidgetType" value="<?= htmlspecialchars($currentType) ?>">
            </div>

            <div class="mb-4">
                <label for="module_id" class="form-label fw-semibold">Module</label>
                <select class="form-select" id="module_id" name="module_id" required>
                    <option value="">— Select Module —</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= isset($widget) && $widget['module_id'] == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 <?= in_array($currentType, ['count']) ? 'd-none' : '' ?>" id="fieldGroup">
                <label for="field_id" class="form-label fw-semibold">Field <span class="text-muted small">(for sum/avg/chart)</span></label>
                <select class="form-select" id="field_id" name="field_id">
                    <option value="">— Select Field —</option>
                    <?php foreach ($modules as $m): ?>
                        <?php if (!empty($fieldsByModule[$m['id']])): ?>
                        <optgroup label="<?= htmlspecialchars($m['name']) ?>" data-module-id="<?= $m['id'] ?>"
                                  style="<?= isset($widget) && $widget['module_id'] == $m['id'] ? '' : 'display:none;' ?>">
                            <?php foreach ($fieldsByModule[$m['id']] as $f): ?>
                            <option value="<?= $f['id'] ?>" data-module="<?= $m['id'] ?>"
                                    <?= isset($widget) && $widget['field_id'] == $f['id'] ? 'selected' : '' ?>>
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
                        $currentColor = isset($widget) ? $widget['chart_color'] : '#6366f1';
                        foreach ($colors as $c): ?>
                        <button type="button" class="color-dot" data-color="<?= $c ?>"
                                style="background:<?= $c ?>" onclick="document.getElementById('chart_color').value='<?= $c ?>'"></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="color" class="form-control form-control-color" id="chart_color"
                           name="chart_color" value="<?= htmlspecialchars($currentColor) ?>">
                </div>
                <div class="col-md-6">
                    <label for="width" class="form-label fw-semibold">Width (Bootstrap Columns)</label>
                    <select class="form-select" id="width" name="width">
                        <?php
                        $currentWidth = isset($widget) ? $widget['width'] : 4;
                        $widths = [3 => '3 — Quarter (4 per row)', 4 => '4 — Third (3 per row)', 6 => '6 — Half (2 per row)', 12 => '12 — Full width'];
                        foreach ($widths as $w => $label): ?>
                        <option value="<?= $w ?>" <?= $currentWidth == $w ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi <?= isset($widget) ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i> 
                    <?= isset($widget) ? 'Save Changes' : 'Add Widget' ?>
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

// Activate first radio visually if not set
if (!document.querySelector('.widget-type-option.active')) {
    document.querySelector('.widget-type-option')?.classList.add('active');
}
</script>
