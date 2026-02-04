<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Отчёт SLA</h1>
        <p class="text-muted mb-0">Соблюдение уровня обслуживания</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Дата с</label>
                <input type="date" class="form-control" name="date_from" value="<?= $this->e($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Дата по</label>
                <input type="date" class="form-control" name="date_to" value="<?= $this->e($dateTo) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Очередь</label>
                <select class="form-select" name="queue">
                    <option value="">Все очереди</option>
                    <?php foreach ($queueList as $queue): ?>
                    <option value="<?= $this->e($queue['name']) ?>" <?= $queueFilter === $queue['name'] ? 'selected' : '' ?>>
                        <?= $this->e($queue['display_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Показать
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SLA Cards -->
<div class="row mb-4">
    <?php foreach ($slaData as $queue): ?>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-header">
                <strong><?= $this->e($queue['display_name']) ?></strong>
                <span class="float-end text-muted">Порог: <?= $queue['sla_threshold'] ?>с</span>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php
                    $slaColor = $queue['sla_percentage'] >= 80 ? 'success' : ($queue['sla_percentage'] >= 60 ? 'warning' : 'danger');
                    ?>
                    <div class="display-4 text-<?= $slaColor ?>"><?= $queue['sla_percentage'] ?>%</div>
                    <small class="text-muted">Соответствие SLA</small>
                </div>
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-<?= $slaColor ?>" role="progressbar" style="width: <?= $queue['sla_percentage'] ?>%">
                        <?= $queue['within_sla'] ?> зв.
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col">
                        <strong><?= $queue['total_answered'] ?></strong>
                        <br><small class="text-muted">Всего отвечено</small>
                    </div>
                    <div class="col">
                        <strong class="text-success"><?= $queue['within_sla'] ?></strong>
                        <br><small class="text-muted">В пределах SLA</small>
                    </div>
                    <div class="col">
                        <strong class="text-danger"><?= $queue['outside_sla'] ?></strong>
                        <br><small class="text-muted">За пределами SLA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($slaData)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-4">
        Нет данных за выбранный период
    </div>
</div>
<?php endif; ?>

<!-- Trend Chart -->
<?php if (!empty($trend)): ?>
<div class="card">
    <div class="card-header">Динамика по дням</div>
    <div class="card-body">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>
<?php endif; ?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="/areports/vendor/chartjs/chart.min.js"></script>
<script>
<?php if (!empty($trend)): ?>
const trendData = <?= json_encode($trend) ?>;
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [{
            label: 'Всего',
            data: trendData.map(d => d.total_calls),
            borderColor: 'rgba(54, 162, 235, 1)',
            tension: 0.1,
            fill: false
        }, {
            label: 'Отвечено',
            data: trendData.map(d => d.answered),
            borderColor: 'rgba(40, 167, 69, 1)',
            tension: 0.1,
            fill: false
        }, {
            label: 'Потеряно',
            data: trendData.map(d => d.abandoned),
            borderColor: 'rgba(220, 53, 69, 1)',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
<?php endif; ?>
</script>
<?php $this->endSection(); ?>
