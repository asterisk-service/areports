<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Производительность операторов</h1>
        <p class="text-muted mb-0">Метрики по операторам</p>
    </div>
    <?php if ($this->can('reports.agent.export')): ?>
    <a href="/areports/reports/agent/export?date_from=<?= $this->e($dateFrom) ?>&date_to=<?= $this->e($dateTo) ?>&agent=<?= $this->e($agentFilter ?? '') ?>" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Экспорт CSV
    </a>
    <?php endif; ?>
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
                <label class="form-label">Оператор</label>
                <select class="form-select" name="agent">
                    <option value="">Все операторы</option>
                    <?php foreach ($agentList as $agent): ?>
                    <option value="<?= $this->e($agent['agent']) ?>" <?= $agentFilter === $agent['agent'] ? 'selected' : '' ?>>
                        <?= $this->e($agent['display_name']) ?>
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

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Обработано звонков</h6>
                <h2 class="mb-0"><?= number_format($totals['calls_handled']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title">Пропущено</h6>
                <h2 class="mb-0"><?= number_format($totals['calls_missed']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">% ответа</h6>
                <h2 class="mb-0"><?= $totals['answer_rate'] ?>%</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Общее время разговора</h6>
                <h2 class="mb-0"><?= $this->formatDuration($totals['total_talk_time']) ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Agent Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Оператор</th>
                        <th class="text-center">Обработано</th>
                        <th class="text-center">Пропущено</th>
                        <th class="text-center">% ответа</th>
                        <th class="text-center">Общ. разговор</th>
                        <th class="text-center">Ср. разговор</th>
                        <th class="text-center">Общ. ожидание</th>
                        <th class="text-center">Ср. ожидание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agents as $agent): ?>
                    <tr>
                        <td>
                            <strong><?= $this->e($agent['display_name']) ?></strong>
                            <br><small class="text-muted"><?= $this->e($agent['agent']) ?></small>
                        </td>
                        <td class="text-center"><?= number_format($agent['calls_handled']) ?></td>
                        <td class="text-center"><?= number_format($agent['calls_missed']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $agent['answer_rate'] >= 80 ? 'success' : ($agent['answer_rate'] >= 60 ? 'warning' : 'danger') ?>">
                                <?= $agent['answer_rate'] ?>%
                            </span>
                        </td>
                        <td class="text-center"><?= $this->formatDuration($agent['total_talk_time']) ?></td>
                        <td class="text-center"><?= $this->formatDuration($agent['avg_talk_time']) ?></td>
                        <td class="text-center"><?= $this->formatDuration($agent['total_hold_time']) ?></td>
                        <td class="text-center"><?= $this->formatDuration($agent['avg_hold_time']) ?></td>
                        <td>
                            <a href="/areports/reports/agent/<?= urlencode($agent['agent']) ?>?date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($agents)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Нет данных за выбранный период</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
<?php if (!empty($agents)): ?>
const topAgents = <?= json_encode(array_slice($agents, 0, 10)) ?>;
<?php endif; ?>
</script>
<?php $this->endSection(); ?>
