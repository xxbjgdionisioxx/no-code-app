<?php
$flash = \Core\Session::flash('flash');
if ($flash && isset($flash['type'], $flash['message'])):
    $type = $flash['type'] === 'success' ? 'success' : 'danger';
    $icon = $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
?>
<div class="alert alert-<?= $type ?> alert-dismissible d-flex align-items-center gap-2 fade show mb-4" role="alert">
    <i class="bi <?= $icon ?>"></i>
    <span><?= htmlspecialchars($flash['message']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
