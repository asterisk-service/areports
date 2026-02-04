<?php $this->section('content'); ?>
<?php
$selectedQueues = is_array($queueFilter) ? $queueFilter : ($queueFilter ? [$queueFilter] : []);
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('reports.queue_summary') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('reports.queue_summary_subtitle') ?></p>
    </div>
    <?php if ($this->can('reports.queue.export')): ?>
    <a href="/areports/reports/queue/export?date_from=<?= $this->e($dateFrom) ?>&date_to=<?= $this->e($dateTo) ?><?php foreach ($selectedQueues as $q): ?>&queue[]=<?= urlencode($q) ?><?php endforeach; ?>" class="btn btn-success">
        <i class="fas fa-download me-1"></i> <?= $this->__('common.export_csv') ?>
    </a>
    <?php endif; ?>
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
                <label class="form-label"><?= $this->__('reports.queues') ?></label>
                <select class="form-select" name="queue[]" id="queue-filter" multiple>
                    <?php foreach ($queueList as $queue): ?>
                    <option value="<?= $this->e($queue['name']) ?>"
                        <?= in_array($queue['name'], $selectedQueues) ? 'selected' : '' ?>>
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

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title"><?= $this->__('reports.total_calls') ?></h6>
                <h2 class="mb-0"><?= number_format($totals['total_calls']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title"><?= $this->__('reports.answered') ?></h6>
                <h2 class="mb-0"><?= number_format($totals['answered']) ?></h2>
                <small><?= $totals['answer_rate'] ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title"><?= $this->__('reports.abandoned') ?></h6>
                <h2 class="mb-0"><?= number_format($totals['abandoned']) ?></h2>
                <small><?= $totals['abandon_rate'] ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title"><?= $this->__('reports.answer_rate') ?></h6>
                <h2 class="mb-0"><?= $totals['answer_rate'] ?>%</h2>
            </div>
        </div>
    </div>
</div>

<!-- Hourly Chart -->
<div class="card mb-4">
    <div class="card-header"><?= $this->__('reports.trend_hourly') ?></div>
    <div class="card-body">
        <canvas id="hourlyChart" height="80"></canvas>
    </div>
</div>

<!-- Queue Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= $this->__('reports.queue') ?></th>
                        <th class="text-center"><?= $this->__('reports.total_calls') ?></th>
                        <th class="text-center"><?= $this->__('reports.answered') ?></th>
                        <th class="text-center"><?= $this->__('reports.abandoned') ?></th>
                        <th class="text-center"><?= $this->__('reports.timeout') ?></th>
                        <th class="text-center"><?= $this->__('reports.answer_rate') ?></th>
                        <th class="text-center"><?= $this->__('reports.avg_wait_time') ?></th>
                        <th class="text-center"><?= $this->__('reports.avg_talk_time') ?></th>
                        <th class="text-center"><?= $this->__('reports.max_wait_time') ?></th>
                        <th class="text-center"><?= $this->__('reports.agents_count') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queues as $queue): ?>
                    <tr>
                        <td>
                            <strong><?= $this->e($queue['display_name']) ?></strong>
                            <br><small class="text-muted"><?= $this->e($queue['queuename']) ?></small>
                        </td>
                        <td class="text-center"><?= number_format($queue['total_calls']) ?></td>
                        <td class="text-center text-success"><?= number_format($queue['answered']) ?></td>
                        <td class="text-center text-danger"><?= number_format($queue['abandoned']) ?></td>
                        <td class="text-center"><?= number_format($queue['timeout']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $queue['answer_rate'] >= 80 ? 'success' : ($queue['answer_rate'] >= 60 ? 'warning' : 'danger') ?>">
                                <?= $queue['answer_rate'] ?>%
                            </span>
                        </td>
                        <td class="text-center"><?= $this->formatDuration($queue['avg_wait_time']) ?></td>
                        <td class="text-center"><?= $this->formatDuration($queue['avg_talk_time']) ?></td>
                        <td class="text-center"><?= $this->formatDuration($queue['max_wait_time']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= $queue['agents_count'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($queues)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4"><?= $this->__('reports.no_data_period') ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="/areports/vendor/chartjs/chart.min.js"></script>
<script>
// Select2 for multi-queue filter
$(document).ready(function() {
    $('#queue-filter').select2({
        theme: 'bootstrap-5',
        placeholder: '<?= $this->__('reports.all_queues') ?>',
        allowClear: true,
        width: '100%'
    });
});

const hourlyData = <?= json_encode($hourly) ?>;
const ctx = document.getElementById('hourlyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: hourlyData.map(h => h.hour + ':00'),
        datasets: [{
            label: '<?= $this->__('reports.total_calls') ?>',
            data: hourlyData.map(h => h.total_calls),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: '<?= $this->__('reports.answered') ?>',
            data: hourlyData.map(h => h.answered),
            backgroundColor: 'rgba(40, 167, 69, 0.5)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }, {
            label: '<?= $this->__('reports.abandoned') ?>',
            data: hourlyData.map(h => h.abandoned),
            backgroundColor: 'rgba(220, 53, 69, 0.5)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php $this->endSection(); ?>
