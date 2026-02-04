<?php
/**
 * Calibration Sessions List View
 */
$sessions = $sessions ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $this->__('quality.calibration') ?></h1>
        <?php if ($this->can('calibration.manage')): ?>
        <a href="/areports/calibration/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i><?= $this->__('quality.create_session') ?>
        </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($sessions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-balance-scale fa-3x text-muted mb-3"></i>
                <p class="text-muted">No calibration sessions found</p>
                <?php if ($this->can('calibration.manage')): ?>
                <a href="/areports/calibration/create" class="btn btn-primary">
                    <?= $this->__('quality.create_session') ?>
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Form</th>
                            <th>Call</th>
                            <th><?= $this->__('quality.participants') ?></th>
                            <th><?= $this->__('common.status') ?></th>
                            <th><?= $this->__('admin.created_at') ?></th>
                            <th><?= $this->__('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td>
                                <a href="/areports/calibration/<?= $session['id'] ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($session['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($session['form_name'] ?? '-') ?></td>
                            <td>
                                <?php if (isset($session['uniqueid'])): ?>
                                <code><?= htmlspecialchars(substr($session['uniqueid'], 0, 20)) ?>...</code>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $session['participant_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'secondary'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$session['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($session['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($session['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/areports/calibration/<?= $session['id'] ?>" class="btn btn-outline-primary" title="<?= $this->__('common.view') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($session['status'] === 'completed'): ?>
                                    <a href="/areports/calibration/<?= $session['id'] ?>/results" class="btn btn-outline-success" title="<?= $this->__('quality.results') ?>">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
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
