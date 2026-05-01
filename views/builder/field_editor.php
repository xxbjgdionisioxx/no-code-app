<!-- Field Editor — Create or Edit a field definition -->
<div class="builder-panels">

    <!-- Left: Field Properties -->
    <div class="builder-panel-left">
        <div class="panel-header"><i class="bi bi-input-cursor-text me-2"></i>
            <?= $field ? 'Edit Field' : 'New Field' ?>
        </div>

        <form method="POST"
              action="<?= $field
                ? APP_URL."/apps/{$app['id']}/modules/{$module['id']}/fields/{$field['id']}"
                : APP_URL."/apps/{$app['id']}/modules/{$module['id']}/fields"
              ?>"
              class="p-3" id="fieldForm">
            <?= \Core\CSRF::field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Field Label <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" name="name" id="fieldLabel"
                       value="<?= htmlspecialchars($field['name'] ?? '') ?>"
                       placeholder="e.g., Product Name" required autofocus>
                <div class="form-text">Auto-generates a slug for database storage.</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Field Type <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="field_type" id="fieldTypeSelect">
                    <?php
                    $typeLabels = [
                        'text'     => '📝 Text',
                        'textarea' => '📄 Textarea',
                        'number'   => '🔢 Number',
                        'date'     => '📅 Date',
                        'email'    => '✉️ Email',
                        'dropdown' => '📋 Dropdown',
                        'checkbox' => '☑️ Checkbox',
                        'file'     => '📎 File Upload',
                        'lookup'   => '🔗 Lookup (Link)',
                    ];
                    $currentType = $field['field_type'] ?? 'text';
                    foreach ($types as $t):
                    ?>
                    <option value="<?= $t ?>" <?= $t === $currentType ? 'selected':'' ?>>
                        <?= $typeLabels[$t] ?? ucfirst($t) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dropdown-specific: choices -->
            <div class="mb-3 <?= $currentType !== 'dropdown' ? 'd-none' : '' ?>" id="choicesGroup">
                <label class="form-label small fw-semibold">Choices</label>
                <textarea class="form-control form-control-sm" name="choices" rows="5"
                          placeholder="One per line:&#10;Option A&#10;Option B&#10;Option C"><?php
                    if ($field && !empty($field['options']['choices'])) {
                        echo htmlspecialchars(implode("\n", $field['options']['choices']));
                    }
                ?></textarea>
            </div>
            
            <!-- Lookup-specific: target module -->
            <div class="mb-3 <?= $currentType !== 'lookup' ? 'd-none' : '' ?>" id="lookupGroup">
                <label class="form-label small fw-semibold">Target Module <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="target_module_id" id="targetModuleSelect">
                    <option value="">— Select Module —</option>
                    <?php
                    $allMods = (new \Engine\ModuleEngine(getDB()))->listModules($app['id']);
                    $targetId = $field['options']['target_module_id'] ?? null;
                    foreach ($allMods as $m):
                    ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $targetId ? 'selected':'' ?>>
                        <?= htmlspecialchars($m['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Select the module this field should link to.</div>
            </div>

            <!-- Validation -->
            <div class="mb-3 <?= in_array($currentType, ['number']) ? '' : 'd-none' ?>" id="numValidation">
                <label class="form-label small fw-semibold">Min / Max Value</label>
                <div class="row g-2">
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" name="v_min"
                               placeholder="Min" value="<?= htmlspecialchars($field['validation']['min'] ?? '') ?>">
                    </div>
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" name="v_max"
                               placeholder="Max" value="<?= htmlspecialchars($field['validation']['max'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3 <?= in_array($currentType, ['text','textarea']) ? '' : 'd-none' ?>" id="textValidation">
                <label class="form-label small fw-semibold">Min / Max Length</label>
                <div class="row g-2">
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" name="v_min_length"
                               placeholder="Min chars" value="<?= htmlspecialchars($field['validation']['min_length'] ?? '') ?>">
                    </div>
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" name="v_max_length"
                               placeholder="Max chars" value="<?= htmlspecialchars($field['validation']['max_length'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Placeholder Text</label>
                <input type="text" class="form-control form-control-sm" name="placeholder"
                       value="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                       placeholder="Hint shown inside the input...">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Help Text</label>
                <input type="text" class="form-control form-control-sm" name="help_text"
                       value="<?= htmlspecialchars($field['help_text'] ?? '') ?>"
                       placeholder="Shown below the field...">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Default Value</label>
                <input type="text" class="form-control form-control-sm" name="default_value"
                       value="<?= htmlspecialchars($field['default_value'] ?? '') ?>"
                       placeholder="Pre-filled value...">
            </div>

            <hr>
            <!-- Toggles -->
            <div class="mb-2">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="is_required" id="isRequired" value="1"
                           <?= ($field['is_required'] ?? false) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="isRequired">Required field</label>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="is_unique" id="isUnique" value="1"
                           <?= ($field['is_unique'] ?? false) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="isUnique">Must be unique</label>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="is_searchable" id="isSearchable" value="1"
                           <?= ($field['is_searchable'] ?? true) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="isSearchable">Searchable</label>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="show_in_list" id="showInList" value="1"
                           <?= ($field['show_in_list'] ?? true) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="showInList">Show as column in list</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                    <i class="bi bi-save me-1"></i> <?= $field ? 'Save Changes' : 'Add Field' ?>
                </button>
                <?php if ($field): ?>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/fields/<?= $field['id'] ?>/delete"
                   class="btn btn-outline-danger btn-sm"
                   data-confirm="Delete this field and all associated data?">
                    <i class="bi bi-trash"></i>
                </a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/builder"
                   class="btn btn-outline-secondary btn-sm">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Center: Preview Updates -->
    <div class="builder-panel-center">
        <div class="panel-header"><i class="bi bi-eye me-2"></i> Field Preview</div>
        <div class="p-4" id="liveFieldPreview">
            <!-- Populated by form_preview.js as user types -->
        </div>
    </div>

    <!-- Right: Field type reference -->
    <div class="builder-panel-right">
        <div class="panel-header"><i class="bi bi-info-circle me-2"></i> Field Type Guide</div>
        <div class="p-3 small text-muted">
            <div class="mb-3">
                <strong class="text-white">text</strong> — Single-line text input. Use for names, titles, codes.
            </div>
            <div class="mb-3">
                <strong class="text-white">textarea</strong> — Multi-line text. Use for descriptions, notes.
            </div>
            <div class="mb-3">
                <strong class="text-white">number</strong> — Numeric values. Supports min/max validation.
            </div>
            <div class="mb-3">
                <strong class="text-white">date</strong> — Date picker (YYYY-MM-DD format).
            </div>
            <div class="mb-3">
                <strong class="text-white">email</strong> — Validated email address input.
            </div>
            <div class="mb-3">
                <strong class="text-white">dropdown</strong> — Single select from predefined choices.
            </div>
            <div class="mb-3">
                <strong class="text-white">checkbox</strong> — Boolean yes/no toggle.
            </div>
            <div class="mb-3">
                <strong class="text-white">file</strong> — File upload (images, PDFs, Excel). Max 10MB.
            </div>
        </div>
    </div>
</div>

<script>
const typeSelect = document.getElementById('fieldTypeSelect');
const choicesGrp = document.getElementById('choicesGroup');
const numVal     = document.getElementById('numValidation');
const txtVal     = document.getElementById('textValidation');
const labelInput = document.getElementById('fieldLabel');

function updateVisibility() {
    const t = typeSelect.value;
    choicesGrp.classList.toggle('d-none', t !== 'dropdown');
    document.getElementById('lookupGroup').classList.toggle('d-none', t !== 'lookup');
    numVal.classList.toggle('d-none',     t !== 'number');
    txtVal.classList.toggle('d-none',     !['text','textarea'].includes(t));
    updatePreview();
}

function updatePreview() {
    const label = labelInput.value || 'Field Label';
    const type  = typeSelect.value;
    const req   = document.getElementById('isRequired').checked;

    let input = '';
    if (type === 'textarea') {
        input = `<textarea class="form-control" placeholder="Enter ${label.toLowerCase()}..." rows="3" disabled></textarea>`;
    } else if (type === 'dropdown') {
        const choices = document.querySelector('[name="choices"]')?.value.split('\n').filter(Boolean) || [];
        input = `<select class="form-select" disabled><option>— Select —</option>${choices.map(c=>`<option>${c}</option>`).join('')}</select>`;
    } else if (type === 'checkbox') {
        input = `<div class="form-check"><input type="checkbox" class="form-check-input" disabled><label class="form-check-label">Yes</label></div>`;
    } else if (type === 'file') {
        input = `<input type="file" class="form-control" disabled>`;
    } else if (type === 'lookup') {
        input = `<select class="form-select" disabled><option>— Select Linked Record —</option></select>`;
    } else {
        input = `<input type="${type === 'email' ? 'email' : type === 'number' ? 'number' : type === 'date' ? 'date' : 'text'}" class="form-control" placeholder="Enter ${label.toLowerCase()}..." disabled>`;
    }

    document.getElementById('liveFieldPreview').innerHTML = `
        <div class="mb-3 field-group">
            <label class="form-label fw-semibold">${label}${req ? ' <span class="text-danger">*</span>' : ''}</label>
            ${input}
        </div>
    `;
}

typeSelect.addEventListener('change', updateVisibility);
labelInput.addEventListener('input', updatePreview);
document.querySelector('[name="choices"]')?.addEventListener('input', updatePreview);
document.getElementById('isRequired').addEventListener('change', updatePreview);

// Initial preview
updatePreview();
</script>
