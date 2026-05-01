<div class="row justify-content-center py-4">
    <div class="col-xl-8 col-lg-10">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="user-avatar user-avatar-xl">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="h3 fw-bold mb-0">Account Settings</h1>
                <p class="text-muted mb-0">Manage your profile and credentials</p>
            </div>
        </div>

        <div class="card bg-dark border-secondary shadow-sm mb-4">
            <div class="card-header border-secondary bg-transparent py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= APP_URL ?>/account" method="POST" class="needs-validation" novalidate>
                    <?= \Core\CSRF::field() ?>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="form-text">This is your display name across the platform.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-text">Used for login and notifications.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">User ID</label>
                            <input type="text" class="form-control bg-transparent border-secondary text-muted" value="#<?= $user['id'] ?>" readonly>
                            <div class="form-text">Your unique system identifier.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Role</label>
                            <div class="d-flex align-items-center mt-2">
                                <?php if ($user['is_admin']): ?>
                                <span class="badge bg-primary px-3 py-2"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                                <?php else: ?>
                                <span class="badge bg-secondary px-3 py-2"><i class="bi bi-person me-1"></i>Standard User</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-secondary opacity-25">

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card bg-dark border-secondary shadow-sm">
            <div class="card-header border-secondary bg-transparent py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Security & Credentials</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1">Password</h6>
                        <p class="text-muted small mb-0">Last changed: Never (managed via password hash)</p>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="bi bi-key me-1"></i> Change Password
                    </button>
                </div>
                <div class="alert alert-info border-0 bg-opacity-10 mt-3 mb-0 d-flex gap-3">
                    <i class="bi bi-info-circle-fill fs-4"></i>
                    <div class="small">
                        Password management is currently locked for this demo. In a production environment, this would trigger a secure password reset flow.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
