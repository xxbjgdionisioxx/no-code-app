<?php
// Partials receive the current $app and $user from the layout scope
$user    = $user ?? \Core\Session::get('user');
$currentApp = $app ?? null;
$modules = [];
if ($currentApp) {
    $modEngine = new \Engine\ModuleEngine(getDB());
    $modules   = $modEngine->listModules($currentApp['id']);
}
?>
<nav class="navbar navbar-dark navbar-expand-lg app-navbar px-3">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>">
        <div class="brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <span class="fw-bold">AppForge</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
        <?php if ($currentApp): ?>
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <?php foreach ($modules as $m): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/<?= $m['slug'] ?>">
                    <i class="bi <?= htmlspecialchars($m['icon']) ?>"></i>
                    <?= htmlspecialchars($m['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <ul class="navbar-nav ms-auto gap-2 mt-3 mt-lg-0">
            <?php if ($user): ?>
            <?php
            // Unread notification count
            $notifStmt = getDB()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
            $notifStmt->execute([$user['id']]);
            $notifCount = (int)$notifStmt->fetchColumn();
            ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" href="#" title="Notifications">
                    <div class="position-relative">
                        <i class="bi bi-bell"></i>
                        <?php if ($notifCount > 0): ?>
                        <span class="notification-badge"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="d-lg-none">Notifications</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                    <div class="user-avatar user-avatar-sm"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <span><?= htmlspecialchars($user['name']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/apps"><i class="bi bi-grid me-2"></i>My Apps</a></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/account"><i class="bi bi-person me-2"></i>Account Settings</a></li>
                    <?php if ($user['is_admin'] && $currentApp): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">App Admin</h6></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/roles"><i class="bi bi-shield me-2"></i>Roles & Permissions</a></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/users"><i class="bi bi-people me-2"></i>Users</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/delete" onclick="return confirm('Are you sure you want to completely delete this app and all its data?')"><i class="bi bi-trash me-2"></i>Delete App</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
            <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
