<!-- Dynamic Record List View -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">
            <i class="bi <?= htmlspecialchars($module['icon']) ?> me-2"></i>
            <?= htmlspecialchars($module['name']) ?>
        </h1>
        <p class="text-muted mb-0"><?= number_format($result['total']) ?> record<?= $result['total'] !== 1 ? 's' : '' ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($perms['can_create']): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/create"
           class="btn btn-primary" id="new-record-btn">
            <i class="bi bi-plus-lg me-1"></i> New <?= htmlspecialchars($module['name']) ?>
        </a>
        <?php endif; ?>
        <?php if (!empty($user['is_admin'])): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/modules/<?= $module['id'] ?>/builder"
           class="btn btn-outline-secondary" title="Open Builder">
            <i class="bi bi-tools"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search Bar -->
<form method="GET" class="mb-4">
    <div class="input-group input-group-sm" style="max-width:400px;">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Search <?= htmlspecialchars($module['name']) ?>...">
        <?php if ($search): ?>
        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x"></i>
        </a>
        <?php endif; ?>
        <button type="submit" class="btn btn-outline-primary">Search</button>
    </div>
</form>

<?php if (empty($result['records'])): ?>
<div class="empty-state text-center py-5">
    <div class="empty-icon mb-3"><i class="bi bi-inbox"></i></div>
    <h3 class="h5 text-white"><?= $search ? 'No results found' : 'No records yet' ?></h3>
    <p class="text-muted mb-4">
        <?= $search ? "Try a different search term." : "Create your first {$module['name']} record." ?>
    </p>
    <?php if ($perms['can_create'] && !$search): ?>
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create First Record
    </a>
    <?php endif; ?>
</div>
<?php else: ?>

<!-- Records Table -->
<div class="table-responsive rounded border border-secondary">
    <table class="table table-dark table-hover align-middle mb-0" id="recordsTable">
        <thead>
            <tr>
                <th class="ps-3" style="width:60px;">#ID</th>
                <?php foreach ($listFields as $f): ?>
                <th><?= htmlspecialchars($f['name']) ?></th>
                <?php endforeach; ?>
                <th class="text-muted small">Created</th>
                <th class="pe-3 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result['records'] as $rec): ?>
            <tr>
                <td class="ps-3 text-muted small">#<?= $rec['id'] ?></td>
                <?php foreach ($listFields as $f): ?>
                <td>
                    <?php
                    $val = $rec['values'][$f['slug']] ?? null;
                    // Truncate long text for list display
                    if ($f['field_type'] === 'file' && $val) {
                        echo '<i class="bi bi-paperclip text-muted"></i> <span class="small">' . htmlspecialchars(basename($val)) . '</span>';
                    } elseif ($f['field_type'] === 'checkbox') {
                        echo $val ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                    } elseif ($val && mb_strlen((string)$val) > 60) {
                        echo '<span title="' . htmlspecialchars($val) . '">' . htmlspecialchars(mb_substr($val, 0, 60)) . '…</span>';
                    } else {
                        echo $val !== null && $val !== '' ? htmlspecialchars((string)$val) : '<span class="text-muted">—</span>';
                    }
                    ?>
                </td>
                <?php endforeach; ?>
                <td class="text-muted small"><?= date('M j, Y', strtotime($rec['created_at'])) ?></td>
                <td class="pe-3 text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $rec['id'] ?>"
                           class="btn btn-sm btn-outline-secondary" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if ($perms['can_edit']): ?>
                        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $rec['id'] ?>/edit"
                           class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($perms['can_delete']): ?>
                        <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/<?= $module['slug'] ?>/<?= $rec['id'] ?>/delete"
                           class="btn btn-sm btn-outline-danger" title="Delete"
                           data-confirm="Delete this record?">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($result['pages'] > 1): ?>
<nav class="mt-4 d-flex align-items-center justify-content-between">
    <span class="text-muted small">
        Page <?= $result['page'] ?> of <?= $result['pages'] ?>
        (<?= number_format($result['total']) ?> total)
    </span>
    <ul class="pagination pagination-sm mb-0">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <li class="page-item <?= $p === $result['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>
