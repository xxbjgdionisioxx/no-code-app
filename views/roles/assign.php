<!-- Permission Matrix / User Assignment -->

<?php if (!empty($showUsers)): ?>
<!-- ── User Management ──────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h4 fw-bold mb-0"><i class="bi bi-people me-2"></i>User Management</h1>
    <button class="btn btn-primary btn-sm ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#addUserForm">
        <i class="bi bi-person-plus me-1"></i> Add User
    </button>
</div>

<!-- Add User Form (Collapsed) -->
<div class="collapse mb-4" id="addUserForm">
    <div class="card card-body">
        <h5 class="card-title fw-bold mb-3">Create New User</h5>
        <form method="POST" action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/users" class="row g-3">
            <?= \Core\CSRF::field() ?>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control form-control-sm" required placeholder="John Doe">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" required placeholder="john@example.com">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Default Password</label>
                <input type="text" name="password" class="form-control form-control-sm" value="password123">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Admin Access</label>
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox" name="is_admin" value="1">
                    <label class="form-check-label small">Global Admin</label>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-success w-100">Create</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">User</th>
                    <th>Email</th>
                    <th>Current Roles</th>
                    <th class="pe-4 text-end">Assign Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar user-avatar-sm">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($u['name']) ?>
                            <?php if ($u['is_admin']): ?>
                            <span class="badge bg-warning text-dark small">Admin</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?= $u['roles'] ? '<span class="badge bg-primary">' . htmlspecialchars($u['roles']) . '</span>' : '<span class="text-muted small">No roles</span>' ?>
                    </td>
                    <td class="pe-4 text-end">
                        <form method="POST"
                              action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/users/<?= $u['id'] ?>/role"
                              class="d-inline-flex gap-2">
                            <?= \Core\CSRF::field() ?>
                            <select class="form-select form-select-sm" name="role_id" style="width:auto;">
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="action" value="assign" class="btn btn-sm btn-success" title="Assign">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                            <button name="action" value="revoke" class="btn btn-sm btn-outline-danger" title="Revoke"
                                    data-confirm="Are you sure you want to revoke this role from the user?">
                                <i class="bi bi-dash-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- ── Permission Matrix ────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="bi bi-key me-2"></i>Permissions — <?= htmlspecialchars($role['name']) ?>
        </h1>
        <p class="text-muted small mb-0">Configure CRUD access per module for this role.</p>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/apps/<?= $app['id'] ?>/roles/<?= $role['id'] ?>/permissions">
    <?= \Core\CSRF::field() ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0" data-no-filter="true">
                <thead>
                    <tr>
                        <th class="ps-4">Module</th>
                        <th class="text-center">
                            <i class="bi bi-eye me-1"></i>View
                        </th>
                        <th class="text-center">
                            <i class="bi bi-plus me-1"></i>Create
                        </th>
                        <th class="text-center">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </th>
                        <th class="text-center pe-4">
                            <i class="bi bi-trash me-1"></i>Delete
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perms as $p): ?>
                    <tr>
                        <td class="ps-4 fw-semibold">
                            <?= htmlspecialchars($p['module_name']) ?>
                            <div class="text-muted small">/<code><?= htmlspecialchars($p['module_slug']) ?></code></div>
                        </td>
                        <?php foreach (['can_view','can_create','can_edit','can_delete'] as $perm): ?>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox" class="form-check-input perm-check"
                                       name="grants[<?= $p['module_id'] ?>][<?= $perm ?>]"
                                       id="<?= $perm ?>_<?= $p['module_id'] ?>"
                                       value="1"
                                       <?= $p[$perm] ? 'checked' : '' ?>>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="checkAll">
                    Check All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheckAll">
                    Uncheck All
                </button>
            </div>
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-1"></i> Save Permissions
            </button>
        </div>
    </div>
</form>

<script>
document.getElementById('checkAll')?.addEventListener('click', () =>
    document.querySelectorAll('.perm-check').forEach(c => c.checked = true));
document.getElementById('uncheckAll')?.addEventListener('click', () =>
    document.querySelectorAll('.perm-check').forEach(c => c.checked = false));
</script>

<?php endif; ?>
