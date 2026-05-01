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
        <span class="fw-bold">Modulyn</span>
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
            <li class="nav-item dropdown">
                <?php
                // Fetch latest 8 notifications for the panel
                $notifListStmt = getDB()->prepare(
                    'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 8'
                );
                $notifListStmt->execute([$user['id']]);
                $notifList = $notifListStmt->fetchAll();
                ?>
                <a class="nav-link d-flex align-items-center gap-1 position-relative"
                   href="#"
                   id="notifDropdown"
                   role="button"
                   data-bs-toggle="dropdown"
                   data-bs-auto-close="outside"
                   aria-expanded="false"
                   title="Notifications">
                    <div class="position-relative">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($notifCount > 0): ?>
                        <span class="notification-badge"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="d-lg-none ms-1">Notifications</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0 notif-dropdown" aria-labelledby="notifDropdown">
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom border-secondary">
                        <span class="fw-semibold small">Notifications</span>
                        <?php if ($notifCount > 0): ?>
                        <a href="#" class="small text-accent" id="markAllRead"
                           style="color: var(--accent); text-decoration:none; font-size:.78rem;"
                           onclick="markAllRead(event)">Mark all read</a>
                        <?php endif; ?>
                    </li>
                    <?php if (empty($notifList)): ?>
                    <li class="notif-empty">
                        <i class="bi bi-bell-slash d-block mb-2 fs-4"></i>
                        No notifications
                    </li>
                    <?php else: ?>
                    <?php foreach ($notifList as $n): ?>
                    <li>
                        <a class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>"
                           href="<?= $n['app_id'] ? APP_URL . '/apps/' . $n['app_id'] : '#' ?>"
                           data-notif-id="<?= $n['id'] ?>">
                            <div class="notif-icon">
                                <i class="bi bi-bell"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                                <?php if ($n['message']): ?>
                                <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                                <?php endif; ?>
                                <div class="notif-time"><?= date('M d, g:i a', strtotime($n['created_at'])) ?></div>
                            </div>
                            <?php if (!$n['is_read']): ?>
                            <div class="flex-shrink-0 mt-1">
                                <span style="width:8px;height:8px;border-radius:50%;background:var(--accent);display:inline-block;"></span>
                            </div>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li class="notif-footer">
                        <a href="#" class="text-muted" onclick="markAllRead(event)">Clear all notifications</a>
                    </li>
                    <?php endif; ?>
                </ul>
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
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/delete" data-confirm="WARNING: Deleting '<?= htmlspecialchars($currentApp['name']) ?>' will permanently erase all modules, fields, and records. This cannot be undone!"><i class="bi bi-trash me-2"></i>Delete App</a></li>
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
