<?php $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Оповещения</h1>
        <p class="text-muted mb-0">Настройка и управление оповещениями</p>
    </div>
    <div>
        <a href="/areports/alerts/history" class="btn btn-outline-secondary me-2">
            <i class="fas fa-history me-1"></i> История
        </a>
        <?php if ($this->can('alerts.manage')): ?>
        <a href="/areports/alerts/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Создать
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Метрика</th>
                        <th>Условие</th>
                        <th>Каналы</th>
                        <th>Статус</th>
                        <th>Последнее срабатывание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert): ?>
                    <?php
                        $operatorLabels = [
                            'gt' => '>',
                            'gte' => '>=',
                            'lt' => '<',
                            'lte' => '<=',
                            'eq' => '='
                        ];
                        $channels = json_decode($alert['notification_channels'] ?? '[]', true) ?: [];
                    ?>
                    <tr>
                        <td>
                            <strong><?= $this->e($alert['name']) ?></strong>
                            <?php if (!empty($alert['description'])): ?>
                            <br><small class="text-muted"><?= $this->e($alert['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= $this->e($alert['alert_type']) ?></span></td>
                        <td><?= $this->e($alert['metric']) ?></td>
                        <td>
                            <?= $operatorLabels[$alert['operator']] ?? $alert['operator'] ?>
                            <?= $this->e($alert['threshold_value']) ?>
                        </td>
                        <td>
                            <?php if (in_array('email', $channels)): ?>
                            <span class="badge bg-info me-1"><i class="fas fa-envelope"></i> Email</span>
                            <?php endif; ?>
                            <?php if (in_array('telegram', $channels)): ?>
                            <span class="badge bg-primary"><i class="fab fa-telegram"></i> Telegram</span>
                            <?php endif; ?>
                            <?php if (empty($channels)): ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($alert['is_active']): ?>
                            <span class="badge bg-success">Активно</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Неактивно</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($alert['last_triggered']): ?>
                            <?= date('d/m/Y H:i', strtotime($alert['last_triggered'])) ?>
                            <br><small class="text-muted"><?= $alert['trigger_count'] ?> раз</small>
                            <?php else: ?>
                            <span class="text-muted">Никогда</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->can('alerts.manage')): ?>
                            <a href="/areports/alerts/<?= $alert['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="/areports/alerts/<?= $alert['id'] ?>/delete" style="display:inline"
                                  onsubmit="return confirm('Удалить это оповещение?')">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($alerts)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Оповещения не настроены</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
