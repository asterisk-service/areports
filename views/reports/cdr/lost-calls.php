<?php
/**
 * Lost Calls Report View
 */
$calls = $calls ?? [];
$filters = $filters ?? [];
$stats = $stats ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $this->__('reports.lost_calls_report') ?></h1>
        <div class="btn-group">
            <a href="/areports/reports/cdr/lost-calls/export?<?= http_build_query($filters) ?>&format=csv" class="btn btn-outline-secondary">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <a href="/areports/reports/cdr/lost-calls/export?<?= http_build_query($filters) ?>&format=excel" class="btn btn-outline-secondary">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('reports.date_from') ?></label>
                    <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('reports.date_to') ?></label>
                    <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('reports.queue') ?></label>
                    <select name="queue" class="form-select">
                        <option value=""><?= $this->__('reports.all_queues') ?></option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i><?= $this->__('common.apply') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= number_format($stats['total_lost'] ?? 0) ?></h2>
                    <small><?= $this->__('reports.total_lost_calls') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= number_format($stats['abandoned'] ?? 0) ?></h2>
                    <small><?= $this->__('reports.abandoned') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= number_format($stats['no_answer'] ?? 0) ?></h2>
                    <small><?= $this->__('reports.no_answer') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= gmdate('i:s', $stats['avg_wait_time'] ?? 0) ?></h2>
                    <small><?= $this->__('reports.avg_wait_before_abandon') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Calls Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><?= $this->__('reports.lost_calls_details') ?></h6>
        </div>
        <div class="card-body">
            <?php if (empty($calls)): ?>
            <div class="text-center py-5">
                <i class="fas fa-phone-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted"><?= $this->__('reports.no_lost_calls') ?></p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th><?= $this->__('reports.date_time') ?></th>
                            <th><?= $this->__('reports.caller') ?></th>
                            <th><?= $this->__('reports.queue') ?></th>
                            <th><?= $this->__('reports.wait_time') ?></th>
                            <th><?= $this->__('reports.reason') ?></th>
                            <th><?= $this->__('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($calls as $call): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i:s', strtotime($call['calldate'])) ?></td>
                            <td><?= htmlspecialchars($call['src']) ?></td>
                            <td><?= htmlspecialchars($call['queue'] ?? '-') ?></td>
                            <td><?= gmdate('i:s', $call['wait_time'] ?? 0) ?></td>
                            <td>
                                <span class="badge bg-<?= ($call['reason'] ?? '') === 'ABANDON' ? 'warning' : 'danger' ?>">
                                    <?= htmlspecialchars($call['reason'] ?? $call['disposition']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($call['src']) ?>" class="btn btn-sm btn-outline-success" title="<?= $this->__('reports.call_back') ?>">
                                    <i class="fas fa-phone"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
