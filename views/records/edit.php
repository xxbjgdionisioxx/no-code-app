<!-- Dynamic Edit Form -->
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>"
       class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0 d-flex align-items-center">
            Edit <?= htmlspecialchars($module['name']) ?> #<?= $record['id'] ?>
            <?php if (!empty($user['is_admin'])): ?>
            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/builder"
               class="btn btn-sm btn-outline-secondary ms-3" title="Open Builder">
                <i class="bi bi-tools me-1"></i> Builder
            </a>
            <?php endif; ?>
        </h1>
        <p class="text-muted small mb-0">Update the fields below.</p>
    </div>
</div>

<?php if (!empty($errors['_global'])): ?>
<div class="alert alert-danger d-flex gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div><?= implode('<br>', array_map('htmlspecialchars', $errors['_global'])) ?></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="POST"
              action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>"
              enctype="multipart/form-data"
              class="needs-validation" novalidate>
            <?= \Core\CSRF::field() ?>

            <?php foreach ($schema['fields'] as $field): ?>
                <?php if (!($field['show_in_form'] ?? true)) continue; ?>
                <?php
                $currentVal  = $record['values'][$field['slug']] ?? null;
                $fieldErrors = $errors[$field['slug']] ?? [];
                ?>
                <div class="mb-4">
                    <?= $fieldEngine->renderFormField($field, $currentVal) ?>
                    <?php foreach ($fieldErrors as $err): ?>
                    <div class="invalid-feedback d-block mt-1">
                        <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($err) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>"
                   class="btn btn-outline-secondary">Cancel</a>
                <?php if ($perms['can_delete'] ?? false): ?>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>/delete"
                   class="btn btn-outline-danger ms-auto"
                   data-confirm="Permanently delete this <?= htmlspecialchars($module['name']) ?> record? This cannot be undone.">
                    <i class="bi bi-trash me-1"></i> Delete
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

</div>
</div>
