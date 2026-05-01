<!-- Dashboard View with Chart.js Widgets -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($app['name']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Widget
    </a>
</div>

<?php if (empty($widgets)): ?>
<div class="empty-state text-center py-5">
    <div class="empty-icon mb-3"><i class="bi bi-bar-chart-line"></i></div>
    <h2 class="h5 text-white mb-2">No widgets yet</h2>
    <p class="text-muted mb-4">Add a widget to visualize your data with charts and metrics.</p>
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets/create" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg me-1"></i> Add First Widget
    </a>
</div>
<?php else: ?>

<div class="row g-4" id="widgetGrid">
    <?php foreach ($widgets as $w): ?>
    <div class="col-md-<?= min(12, max(3, (int)$w['width'])) ?>"
         id="widget-col-<?= $w['id'] ?>">
        <div class="widget-card card h-100">
            <div class="widget-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($w['title']) ?></span>
                <div class="d-flex gap-1 align-items-center">
                    <span class="badge text-bg-secondary"><?= htmlspecialchars($w['module_name']) ?></span>
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets/<?= $w['id'] ?>/edit"
                       class="btn btn-sm btn-link text-muted p-0 ms-1"
                       title="Edit widget">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/dashboard/widgets/<?= $w['id'] ?>/delete"
                       class="btn btn-sm btn-link text-muted p-0"
                       title="Remove widget"
                       data-confirm="Remove this widget?">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= htmlspecialchars($w['module_slug']) ?>"
               class="card-body d-flex flex-column align-items-center justify-content-center text-decoration-none widget-body-link"
               style="min-height:200px;">
                <!-- Loading state -->
                <div class="widget-loading" id="loading-<?= $w['id'] ?>">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
                <!-- Metric value (count/sum/average) -->
                <div class="widget-metric d-none" id="metric-<?= $w['id'] ?>">
                    <div class="metric-value" style="color:<?= htmlspecialchars($w['chart_color']) ?>">—</div>
                    <div class="metric-label text-muted small"><?= htmlspecialchars($w['widget_type']) ?></div>
                    <div class="metric-hint text-muted mt-2" style="font-size:.72rem; opacity:.6;">
                        <i class="bi bi-arrow-up-right-circle me-1"></i>View <?= htmlspecialchars($w['module_name']) ?>
                    </div>
                </div>
                <!-- No Data State -->
                <div class="widget-no-data d-none text-muted small text-center" id="no-data-<?= $w['id'] ?>">
                    <i class="bi bi-info-circle mb-1 d-block fs-5"></i>
                    No data available
                </div>
                <!-- Chart canvas (bar/pie/trend) -->
                <div class="widget-chart d-none w-100 px-2" id="chart-container-<?= $w['id'] ?>">
                    <canvas id="chart-<?= $w['id'] ?>" style="max-height:180px;"></canvas>
                </div>
                <!-- Progress Bar -->
                <div class="widget-progress d-none w-100 px-4 text-center" id="progress-<?= $w['id'] ?>">
                    <div class="h2 fw-bold mb-1" id="prog-val-<?= $w['id'] ?>" style="color:<?= htmlspecialchars($w['chart_color']) ?>">0%</div>
                    <div class="progress mb-2" style="height: 10px; background: #1e2535;">
                        <div class="progress-bar" id="prog-bar-<?= $w['id'] ?>" style="background:<?= htmlspecialchars($w['chart_color']) ?>; width: 0%"></div>
                    </div>
                    <div class="small text-muted">Goal: <span id="prog-goal-<?= $w['id'] ?>">0</span></div>
                </div>
                <!-- Top List -->
                <div class="widget-list d-none w-100 px-3" id="list-<?= $w['id'] ?>">
                    <div class="list-group list-group-flush bg-transparent" id="list-items-<?= $w['id'] ?>">
                        <!-- Items injected here -->
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
// Widget data endpoint base URL
const APP_URL = '<?= APP_URL ?>';
const APP_ID  = <?= $app['id'] ?>;

// Load all widgets via AJAX
document.addEventListener('DOMContentLoaded', () => {
    <?php foreach ($widgets as $w): ?>
    loadWidget(<?= $w['id'] ?>, '<?= $w['widget_type'] ?>', '<?= addslashes($w['chart_color']) ?>');
    <?php endforeach; ?>
});

function loadWidget(widgetId, widgetType, color) {
    fetch(`${APP_URL}/apps/${APP_ID}/dashboard/data/${widgetId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const data = res.data;
            const loading = document.getElementById(`loading-${widgetId}`);
            loading?.classList.add('d-none');

            if (['count','sum','average'].includes(widgetType)) {
                const el = document.getElementById(`metric-${widgetId}`);
                el?.classList.remove('d-none');
                el.querySelector('.metric-value').textContent =
                    typeof data.value === 'number' ? data.value.toLocaleString() : data.value;

            } else if (['bar_chart','pie_chart','trend_chart'].includes(widgetType)) {
                const container = document.getElementById(`chart-container-${widgetId}`);
                container?.classList.remove('d-none');
                const canvas = document.getElementById(`chart-${widgetId}`);
                
                let chartType = 'bar';
                if (widgetType === 'pie_chart') chartType = 'doughnut';
                if (widgetType === 'trend_chart') chartType = 'line';

                new Chart(canvas, {
                    type: chartType,
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: res.widget.title,
                            data: data.data,
                            backgroundColor: widgetType === 'pie_chart'
                                ? data.labels.map((_, i) => `hsl(${(i * 47) % 360},65%,55%)`)
                                : color + 'cc',
                            borderColor: color,
                            fill: widgetType === 'trend_chart',
                            tension: 0.4,
                            borderWidth: 2,
                            borderRadius: widgetType === 'bar_chart' ? 4 : 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: widgetType === 'pie_chart' },
                        },
                        scales: widgetType !== 'pie_chart' ? {
                            x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { display: false } },
                            y: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: '#1e2535' } },
                        } : {},
                    }
                });
            } else if (widgetType === 'progress_bar') {
                document.getElementById(`progress-${widgetId}`)?.classList.remove('d-none');
                document.getElementById(`prog-val-${widgetId}`).textContent = data.percent + '%';
                document.getElementById(`prog-bar-${widgetId}`).style.width = data.percent + '%';
                document.getElementById(`prog-goal-${widgetId}`).textContent = data.goal.toLocaleString();
            } else if (widgetType === 'top_list') {
                const listEl = document.getElementById(`list-${widgetId}`);
                listEl?.classList.remove('d-none');
                const itemsContainer = document.getElementById(`list-items-${widgetId}`);
                if (!data.items || data.items.length === 0) {
                     document.getElementById(`no-data-${widgetId}`)?.classList.remove('d-none');
                     listEl?.classList.add('d-none');
                     return;
                }
                itemsContainer.innerHTML = data.items.map(item => `
                    <div class="list-group-item bg-transparent text-white border-secondary px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="small">${item.label || 'Unnamed'}</span>
                        <span class="badge rounded-pill" style="background:${color}33; color:${color}">${parseFloat(item.value).toLocaleString()}</span>
                    </div>
                `).join('');
            }
            
            // Check for empty chart data
            if (['bar_chart','pie_chart','trend_chart'].includes(widgetType) && (!data.labels || data.labels.length === 0)) {
                document.getElementById(`chart-container-${widgetId}`)?.classList.add('d-none');
                document.getElementById(`no-data-${widgetId}`)?.classList.remove('d-none');
            }
        })
        .catch(() => {
            document.getElementById(`loading-${widgetId}`)?.classList.add('d-none');
        });
}
</script>

<?php endif; ?>
