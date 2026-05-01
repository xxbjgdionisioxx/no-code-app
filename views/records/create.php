<!-- Dynamic Create Form -->
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0">New <?= htmlspecialchars($module['name']) ?></h1>
        <p class="text-muted small mb-0">Fill in the fields below to create a new record.</p>
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
              action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>"
              enctype="multipart/form-data"
              class="needs-validation" novalidate>
            <?= \Core\CSRF::field() ?>

            <?php foreach ($schema['fields'] as $field): ?>
                <?php
                // Restore old value if validation failed
                $oldVal = $oldValues["field_{$field['slug']}"] ?? null;
                $fieldErrors = $errors[$field['slug']] ?? [];
                ?>
                <div class="mb-4 <?= !empty($fieldErrors) ? 'has-error' : '' ?>">
                    <?= $fieldEngine->renderFormField($field, $oldVal) ?>
                    <?php foreach ($fieldErrors as $err): ?>
                    <div class="invalid-feedback d-block mt-1">
                        <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($err) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($schema['fields'])): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>
                No fields defined for this module.
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/builder">Add fields in the builder.</a>
            </div>
            <?php endif; ?>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4" id="submit-record">
                    <i class="bi bi-save me-1"></i> Create <?= htmlspecialchars($module['name']) ?>
                </button>
                <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
