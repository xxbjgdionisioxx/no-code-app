<!-- Record Detail / Show View -->
<div class="row justify-content-center">
<div class="col-lg-9">

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="bi <?= htmlspecialchars($module['icon']) ?> me-2"></i>
            <?= htmlspecialchars($module['name']) ?> — Record #<?= $record['id'] ?>
        </h1>
        <p class="text-muted small mb-0">
            Created <?= date('F j, Y \a\t g:ia', strtotime($record['created_at'])) ?>
        </p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <?php if ($perms['can_edit']): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>/edit"
           class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <?php endif; ?>
        <?php if ($perms['can_delete']): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $record['id'] ?>/delete"
           class="btn btn-outline-danger btn-sm"
           data-confirm="Permanently delete this <?= htmlspecialchars($module['name']) ?> record? This cannot be undone.">
            <i class="bi bi-trash me-1"></i> Delete
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-dark table-hover mb-0 align-middle">
            <tbody>
                <?php foreach ($schema['fields'] as $field): ?>
                <tr>
                    <th class="ps-4 py-3 text-muted fw-normal" style="width:220px;">
                        <i class="bi bi-dot me-1"></i>
                        <?= htmlspecialchars($field['name']) ?>
                    </th>
                    <td class="py-3 pe-4">
                        <?= $fieldEngine->renderDisplayValue($field, $record['values'][$field['slug']] ?? null) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="border-top border-secondary">
                    <th class="ps-4 py-3 text-muted fw-normal">Record ID</th>
                    <td class="py-3 pe-4 text-muted"><?= $record['id'] ?></td>
                </tr>
                <tr>
                    <th class="ps-4 py-3 text-muted fw-normal">Created At</th>
                    <td class="py-3 pe-4 text-muted"><?= htmlspecialchars($record['created_at']) ?></td>
                </tr>
                <tr>
                    <th class="ps-4 py-3 text-muted fw-normal">Updated At</th>
                    <td class="py-3 pe-4 text-muted"><?= htmlspecialchars($record['updated_at']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
