<?php
$currentApp = $app ?? null;
$user = $user ?? \Core\Session::get('user');
?>
<?php if ($currentApp): ?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-app-icon" style="background: <?= htmlspecialchars($currentApp['color']) ?>">
            <i class="bi <?= htmlspecialchars($currentApp['icon']) ?>"></i>
        </div>
        <div>
            <div class="sidebar-app-name"><?= htmlspecialchars($currentApp['name']) ?></div>
            <div class="sidebar-app-sub text-muted small">App</div>
        </div>
    </div>

    <div class="sidebar-section-label">Navigation</div>
    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/dashboard" class="sidebar-link">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>

        <?php
        $modEngine = new \Engine\ModuleEngine(getDB());
        $modules   = $modEngine->listModules($currentApp['id']);
        foreach ($modules as $m):
        ?>
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/<?= $m['slug'] ?>" class="sidebar-link">
            <i class="bi <?= htmlspecialchars($m['icon']) ?>"></i>
            <span><?= htmlspecialchars($m['name']) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <?php if (!empty($user['is_admin'])): ?>
    <div class="sidebar-section-label mt-3">Builder</div>
    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>" class="sidebar-link">
            <i class="bi bi-grid-1x2"></i><span>App Overview</span>
        </a>
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/modules/create" class="sidebar-link">
            <i class="bi bi-plus-circle"></i><span>Add Module</span>
        </a>
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/roles" class="sidebar-link">
            <i class="bi bi-shield-check"></i><span>Roles</span>
        </a>
        <a href="<?= APP_URL ?>/apps/<?= $currentApp['id'] ?>/users" class="sidebar-link">
            <i class="bi bi-people"></i><span>Users</span>
        </a>
    </nav>
    <?php endif; ?>
</aside>
<?php endif; ?>
