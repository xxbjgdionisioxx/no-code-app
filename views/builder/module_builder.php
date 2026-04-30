<!-- ============================================================
     Visual Module Builder — Three Panel Layout
     Left: Module config | Center: Field canvas | Right: Live preview
     ============================================================ -->

<div class="builder-panels">

    <!-- ── Left Panel: Module Properties ─────────────────── -->
    <div class="builder-panel-left">
        <div class="panel-header">
            <i class="bi bi-sliders me-2"></i> Module Settings
        </div>

        <form method="POST"
              action="<?= isset($module) ? APP_URL."/apps/{$app['id']}/modules/{$module['id']}" : APP_URL."/apps/{$app['id']}/modules" ?>"
              class="p-3">
            <?= \Core\CSRF::field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Module Name</label>
                <input type="text" class="form-control form-control-sm" name="name"
                       value="<?= htmlspecialchars($module['name'] ?? '') ?>"
                       placeholder="e.g., Products" required id="moduleName">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Description</label>
                <textarea class="form-control form-control-sm" name="description" rows="2"
                          placeholder="What does this module store?"><?= htmlspecialchars($module['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Icon</label>
                <div class="icon-picker-sm">
                    <?php
                    $icons = ['bi-table','bi-people','bi-box','bi-calendar','bi-clipboard','bi-briefcase',
                              'bi-bar-chart','bi-truck','bi-cart','bi-star','bi-building','bi-file-text'];
                    $selIcon = $module['icon'] ?? 'bi-table';
                    foreach ($icons as $ic): ?>
                    <button type="button" class="icon-opt-sm <?= $ic === $selIcon ? 'active':'' ?>"
                            data-icon="<?= $ic ?>"><i class="bi <?= $ic ?>"></i></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="icon" id="modIcon" value="<?= htmlspecialchars($selIcon) ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-save me-1"></i>
                <?= $module ? 'Save Module' : 'Create & Continue' ?>
            </button>
        </form>

        <?php if ($module): ?>
        <hr class="mx-3">
        <!-- Add Field Quick Form -->
        <div class="panel-header"><i class="bi bi-plus-circle me-2"></i> Add Field</div>
        <form method="POST" id="addFieldForm"
              action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/fields"
              class="p-3">
            <?= \Core\CSRF::field() ?>

            <div class="mb-2">
                <input type="text" class="form-control form-control-sm" name="name"
                       placeholder="Field Label" required id="newFieldName">
            </div>

            <div class="mb-2">
                <select class="form-select form-select-sm" name="field_type" id="fieldTypeSelect">
                    <?php
                    $types = (new \Engine\FieldEngine(getDB()))->getRegisteredTypes();
                    $typeLabels = [
                        'text' => 'Text','textarea' => 'Textarea','number' => 'Number',
                        'date' => 'Date','email' => 'Email','dropdown' => 'Dropdown',
                        'checkbox' => 'Checkbox','file' => 'File Upload',
                    ];
                    foreach ($types as $t):
                    ?>
                    <option value="<?= $t ?>"><?= $typeLabels[$t] ?? ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dropdown choices (shown only when type=dropdown) -->
            <div class="mb-2 d-none" id="choicesGroup">
                <textarea class="form-control form-control-sm" name="choices" rows="3"
                          placeholder="One choice per line&#10;Red&#10;Green&#10;Blue"></textarea>
            </div>

            <div class="mb-2 d-flex gap-3">
                <div class="form-check form-check-sm">
                    <input type="checkbox" class="form-check-input" name="is_required" id="fReq" value="1">
                    <label class="form-check-label small" for="fReq">Required</label>
                </div>
                <div class="form-check form-check-sm">
                    <input type="checkbox" class="form-check-input" name="show_in_list" id="fList" value="1" checked>
                    <label class="form-check-label small" for="fList">In List</label>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-sm w-100" id="addFieldBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Field
            </button>
        </form>
        <?php endif; ?>

        <!-- Danger Zone -->
        <?php if ($module): ?>
        <hr class="mx-3">
        <div class="px-3 pb-3">
            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/delete"
               class="btn btn-outline-danger btn-sm w-100"
               onclick="return confirm('Delete this module and all its data?')">
                <i class="bi bi-trash me-1"></i> Delete Module
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Center Panel: Field Canvas ────────────────────── -->
    <div class="builder-panel-center">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-layout-text-sidebar me-2"></i> Fields</span>
            <?php if (!empty($schema['fields'])): ?>
            <span class="badge bg-primary"><?= count($schema['fields']) ?> fields</span>
            <?php endif; ?>
        </div>

        <?php if (empty($module)): ?>
        <div class="empty-panel text-center py-5">
            <i class="bi bi-arrow-left-circle fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted">Fill in the module details on the left to get started.</p>
        </div>
        <?php elseif (empty($schema['fields'])): ?>
        <div class="empty-panel text-center py-5">
            <i class="bi bi-plus-square-dotted fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted">No fields yet. Add your first field using the panel on the left.</p>
        </div>
        <?php else: ?>
        <div class="field-canvas" id="fieldCanvas">
            <?php foreach ($schema['fields'] as $field): ?>
            <div class="field-card" data-field-id="<?= $field['id'] ?>" draggable="true">
                <div class="field-card-drag"><i class="bi bi-grip-vertical"></i></div>
                <div class="field-card-type">
                    <span class="badge bg-secondary"><?= $field['field_type'] ?></span>
                </div>
                <div class="field-card-body">
                    <div class="field-card-name fw-semibold">
                        <?= htmlspecialchars($field['name']) ?>
                        <?php if ($field['is_required']): ?>
                        <span class="text-danger ms-1">*</span>
                        <?php endif; ?>
                    </div>
                    <div class="field-card-meta text-muted small">
                        slug: <code><?= $field['slug'] ?></code>
                        <?php if ($field['is_unique']): ?> · <span class="text-warning">unique</span><?php endif; ?>
                        <?php if (!$field['show_in_list']): ?> · <span class="text-secondary">hidden in list</span><?php endif; ?>
                    </div>
                </div>
                <div class="field-card-actions">
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/fields/<?= $field['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary" title="Edit Field">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/fields/<?= $field['id'] ?>/delete"
                       class="btn btn-sm btn-outline-danger" title="Delete Field"
                       onclick="return confirm('Delete this field? All stored values will be lost.')">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Right Panel: Live Form Preview ────────────────── -->
    <div class="builder-panel-right">
        <div class="panel-header">
            <i class="bi bi-eye me-2"></i> Live Form Preview
        </div>
        <div class="preview-device p-3" id="formPreview">
            <?php if (!empty($schema['fields'])): ?>
                <?php
                $fe = new \Engine\FieldEngine(getDB());
                foreach ($schema['fields'] as $field):
                    echo $fe->renderFormField($field);
                endforeach;
                ?>
                <button class="btn btn-primary w-100 mt-2" disabled>
                    <i class="bi bi-save me-1"></i> Submit (Preview)
                </button>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-layout-text-window fs-2 d-block mb-2"></i>
                Form preview will appear here as you add fields.
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
// Show/hide choices textarea for dropdown type
document.getElementById('fieldTypeSelect')?.addEventListener('change', function () {
    document.getElementById('choicesGroup').classList.toggle('d-none', this.value !== 'dropdown');
});

// Icon picker for module form
document.querySelectorAll('.icon-opt-sm').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.icon-opt-sm').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('modIcon').value = btn.dataset.icon;
    });
});

// Drag-and-drop field reordering
const canvas = document.getElementById('fieldCanvas');
if (canvas) {
    let dragging = null;
    canvas.addEventListener('dragstart', e => {
        dragging = e.target.closest('.field-card');
        dragging?.classList.add('dragging');
    });
    canvas.addEventListener('dragend', () => {
        dragging?.classList.remove('dragging');
        dragging = null;
        // Save new order via AJAX
        const ids = [...canvas.querySelectorAll('.field-card')].map(c => c.dataset.fieldId);
        fetch('<?= APP_URL ?>/apps/<?= $app['id'] ?? 0 ?>/modules/<?= $module['id'] ?? 0 ?>/fields/reorder', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: ids.map(id => `ids[]=${id}`).join('&') + '&<?= CSRF_TOKEN_NAME ?>=<?= \Core\CSRF::token() ?>'
        });
    });
    canvas.addEventListener('dragover', e => {
        e.preventDefault();
        const afterEl = getDragAfterElement(canvas, e.clientY);
        if (afterEl == null) canvas.appendChild(dragging);
        else canvas.insertBefore(dragging, afterEl);
    });
    function getDragAfterElement(container, y) {
        const els = [...container.querySelectorAll('.field-card:not(.dragging)')];
        return els.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) ? {offset, element: child} : closest;
        }, {offset: Number.NEGATIVE_INFINITY}).element;
    }
}
</script>
