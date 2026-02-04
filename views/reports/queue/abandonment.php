<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('reports.abandoned_calls') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('reports.abandoned_subtitle') ?></p>
    </div>
    <span class="badge bg-danger fs-6"><?= number_format($total) ?> <?= $this->__('reports.abandoned') ?></span>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><?= $this->__('reports.date_from') ?></label>
                <input type="date" class="form-control" name="date_from" value="<?= $this->e($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= $this->__('reports.date_to') ?></label>
                <input type="date" class="form-control" name="date_to" value="<?= $this->e($dateTo) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= $this->__('reports.queue') ?></label>
                <select class="form-select" name="queue">
                    <option value=""><?= $this->__('reports.all_queues') ?></option>
                    <?php foreach ($queueList as $queue): ?>
                    <option value="<?= $this->e($queue['name']) ?>" <?= $queueFilter === $queue['name'] ? 'selected' : '' ?>>
                        <?= $this->e($queue['display_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> <?= $this->__('common.show') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Abandoned Calls Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= $this->__('common.time') ?></th>
                        <th><?= $this->__('reports.caller_number') ?></th>
                        <th><?= $this->__('reports.queue') ?></th>
                        <th class="text-center"><?= $this->__('reports.wait_time') ?></th>
                        <th class="text-center"><?= $this->__('reports.position') ?></th>
                        <th><?= $this->__('reports.unique_id') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= $this->formatDateTime($record['time']) ?></td>
                        <td>
                            <strong><?= $this->e($record['caller_id'] ?: $this->__('reports.unknown')) ?></strong>
                            <?php if (!empty($record['caller_name'])): ?>
                            <br><small class="text-muted"><?= $this->e($record['caller_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($record['display_name'] ?? $record['queuename']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= ($record['wait_time'] ?? 0) > 60 ? 'danger' : 'warning' ?>">
                                <?= $this->formatDuration($record['wait_time'] ?? 0) ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $record['wait_position'] ?? '-' ?></td>
                        <td><small class="text-muted"><?= $this->e($record['callid']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4"><?= $this->__('reports.no_data_period') ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <?= $this->pagination($page, $totalPages, '/areports/reports/queue/abandonment', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'queue' => $queueFilter
        ]) ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
