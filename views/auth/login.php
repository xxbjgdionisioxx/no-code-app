<!-- Login Page -->
<form method="POST" action="<?= APP_URL ?>/login" class="needs-validation" novalidate>
    <?= \Core\CSRF::field() ?>
    <h2 class="h5 fw-bold text-white text-center mb-4">Sign in to your account</h2>

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= htmlspecialchars(\Core\Session::getOld('email', '')) ?>"
                   placeholder="you@example.com" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="••••••••" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold" id="login-btn">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>

    <p class="text-center text-muted mt-3 mb-0 small">
        Don't have an account? <a href="<?= APP_URL ?>/register" class="text-primary">Create one</a>
    </p>
</form>
