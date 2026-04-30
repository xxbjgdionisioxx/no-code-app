<!-- Roles Index + Permission Matrix -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-check me-2"></i>Roles</h1>
        <p class="text-muted mb-0">Manage access roles for <?= htmlspecialchars($app['name']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Role
    </a>
</div>

<!-- Create Role Form (inline) -->
<?php if (!empty($creating)): ?>
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Create New Role</h6></div>
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles">
            <?= \Core\CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="name" placeholder="Role name (e.g., Editor)" required autofocus>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="description" placeholder="Short description...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Roles Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Role</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td class="ps-4 fw-semibold">
                        <i class="bi bi-shield me-2 text-primary"></i>
                        <?= htmlspecialchars($role['name']) ?>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($role['description'] ?? '—') ?></td>
                    <td>
                        <?php if ($role['is_system']): ?>
                        <span class="badge bg-warning text-dark">System</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Custom</span>
                        <?php endif; ?>
                    </td>
                    <td class="pe-4 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles/<?= $role['id'] ?>/permissions"
                               class="btn btn-sm btn-outline-primary" title="Manage Permissions">
                                <i class="bi bi-key me-1"></i> Permissions
                            </a>
                            <?php if (!$role['is_system']): ?>
                            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles/<?= $role['id'] ?>/edit"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles/<?= $role['id'] ?>/delete"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete role?')">
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
</div>

<!-- User Management Link -->
<div class="mt-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/users" class="btn btn-outline-secondary">
        <i class="bi bi-people me-1"></i> Manage User Assignments
    </a>
</div>
