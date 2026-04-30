<!-- Register Page -->
<form method="POST" action="<?= APP_URL ?>/register" class="needs-validation" novalidate>
    <?= \Core\CSRF::field() ?>
    <h2 class="h5 fw-bold text-white text-center mb-4">Create your account</h2>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= htmlspecialchars(\Core\Session::getOld('name', '')) ?>"
                   placeholder="Jane Smith" required minlength="2">
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= htmlspecialchars(\Core\Session::getOld('email', '')) ?>"
                   placeholder="you@example.com" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Min. 8 characters" required minlength="8">
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                   placeholder="Repeat password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-person-plus me-1"></i> Create Account
    </button>

    <p class="text-center text-muted mt-3 mb-0 small">
        Already have an account? <a href="<?= APP_URL ?>/login" class="text-primary">Sign in</a>
    </p>
</form>
