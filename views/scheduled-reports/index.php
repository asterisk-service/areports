<?php
/**
 * Scheduled Reports List View
 */
$reports = $reports ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $this->__('menu.scheduled_reports') ?></h1>
        <?php if ($this->can('scheduled-reports.manage')): ?>
        <a href="/areports/scheduled-reports/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i><?= $this->__('common.create') ?> Scheduled Report
        </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($reports)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <p class="text-muted">No scheduled reports configured</p>
                <?php if ($this->can('scheduled-reports.manage')): ?>
                <a href="/areports/scheduled-reports/create" class="btn btn-primary">
                    Create Your First Scheduled Report
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= $this->__('common.name') ?></th>
                            <th><?= $this->__('common.type') ?></th>
                            <th>Schedule</th>
                            <th>Last Run</th>
                            <th>Next Run</th>
                            <th><?= $this->__('common.status') ?></th>
                            <th>Created By</th>
                            <th><?= $this->__('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($report['name']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'cdr' => ['CDR Report', 'info'],
                                    'queue' => ['Queue Report', 'success'],
                                    'agent' => ['Agent Report', 'primary'],
                                    'sla' => ['SLA Report', 'warning']
                                ];
                                $typeInfo = $typeLabels[$report['report_type']] ?? ['Unknown', 'secondary'];
                                ?>
                                <span class="badge bg-<?= $typeInfo[1] ?>"><?= $typeInfo[0] ?></span>
                            </td>
                            <td>
                                <?php
                                $scheduleLabels = [
                                    'daily' => 'Daily',
                                    'weekly' => 'Weekly',
                                    'monthly' => 'Monthly'
                                ];
                                ?>
                                <i class="fas fa-clock me-1"></i>
                                <?= $scheduleLabels[$report['schedule_type']] ?? ucfirst($report['schedule_type']) ?>
                                <?php if (isset($report['schedule_time'])): ?>
                                <small class="text-muted">at <?= htmlspecialchars(substr($report['schedule_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($report['last_run']): ?>
                                <?= date('d/m/Y H:i', strtotime($report['last_run'])) ?>
                                <?php else: ?>
                                <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($report['next_run']): ?>
                                <?= date('d/m/Y H:i', strtotime($report['next_run'])) ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($report['is_active']): ?>
                                <span class="badge bg-success"><?= $this->__('common.active') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= $this->__('common.inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(($report['first_name'] ?? '') . ' ' . ($report['last_name'] ?? '')) ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($this->can('scheduled-reports.manage')): ?>
                                    <a href="/areports/scheduled-reports/<?= $report['id'] ?>/edit" class="btn btn-outline-secondary" title="<?= $this->__('common.edit') ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="<?= $this->__('common.delete') ?>"
                                            onclick="deleteReport(<?= $report['id'] ?>, '<?= htmlspecialchars($report['name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Scheduled Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the scheduled report "<strong id="deleteReportName"></strong>"?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $this->__('common.cancel') ?></button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger"><?= $this->__('common.delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteReport(id, name) {
    document.getElementById('deleteReportName').textContent = name;
    document.getElementById('deleteForm').action = '/areports/scheduled-reports/' + id + '/delete';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
