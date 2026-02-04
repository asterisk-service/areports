<?php $this->section('content'); ?>
<?php
$history = $history ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('alerts.history_title') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('alerts.history_subtitle') ?></p>
    </div>
    <a href="/areports/alerts" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?= $this->__('common.back') ?>
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($history)): ?>
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= $this->__('alerts.no_history') ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= $this->__('alerts.alert') ?></th>
                        <th><?= $this->__('alerts.triggered_at') ?></th>
                        <th><?= $this->__('alerts.triggered_value') ?></th>
                        <th><?= $this->__('alerts.threshold_value') ?></th>
                        <th><?= $this->__('alerts.message') ?></th>
                        <th><?= $this->__('alerts.status') ?></th>
                        <th><?= $this->__('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                    <tr class="<?= $item['acknowledged_at'] ? '' : 'table-warning' ?>">
                        <td>
                            <strong><?= $this->e($item['alert_name']) ?></strong>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i:s', strtotime($item['triggered_at'])) ?>
                        </td>
                        <td>
                            <span class="badge bg-danger"><?= $this->e($item['triggered_value'] ?? '-') ?></span>
                        </td>
                        <td>
                            <?= $this->e($item['threshold_value'] ?? '-') ?>
                        </td>
                        <td>
                            <?= $this->e($item['message'] ?? '-') ?>
                        </td>
                        <td>
                            <?php if ($item['acknowledged_at']): ?>
                            <span class="badge bg-success"><?= $this->__('alerts.acknowledged') ?></span>
                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($item['acknowledged_at'])) ?></small>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$item['acknowledged_at']): ?>
                            <form method="POST" action="/areports/alerts/<?= $item['id'] ?>/acknowledge" style="display:inline">
                                <button type="submit" class="btn btn-sm btn-success" title="<?= $this->__('alerts.acknowledge') ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>"><?= $this->__('common.previous') ?></a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>"><?= $this->__('common.next') ?></a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
