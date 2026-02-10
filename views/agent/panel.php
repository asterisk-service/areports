<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('agent.title') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('agent.extension') ?>: <strong><?= $this->e($extension) ?></strong></p>
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-success" id="btn-login-all" title="<?= $this->__('agent.login_all') ?>">
            <i class="fas fa-sign-in-alt me-1"></i> <?= $this->__('agent.login_all') ?>
        </button>
        <button type="button" class="btn btn-danger" id="btn-logout-all" title="<?= $this->__('agent.logout_all') ?>">
            <i class="fas fa-sign-out-alt me-1"></i> <?= $this->__('agent.logout_all') ?>
        </button>
        <button type="button" class="btn btn-warning" id="btn-pause-all" data-bs-toggle="modal" data-bs-target="#pauseModal" data-queue="" title="<?= $this->__('agent.pause_all') ?>">
            <i class="fas fa-pause me-1"></i> <?= $this->__('agent.pause_all') ?>
        </button>
        <button type="button" class="btn btn-info" id="btn-unpause-all" title="<?= $this->__('agent.unpause_all') ?>">
            <i class="fas fa-play me-1"></i> <?= $this->__('agent.unpause_all') ?>
        </button>
    </div>
</div>

<div class="row">
    <!-- Today's Stats -->
    <div class="col-lg-8">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= number_format($todayStats['total_calls'] ?? 0) ?></h3>
                        <small><?= $this->__('agent.calls_today') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= number_format($todayStats['answered'] ?? 0) ?></h3>
                        <small><?= $this->__('agent.answered') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= number_format($todayStats['missed'] ?? 0) ?></h3>
                        <small><?= $this->__('agent.missed') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $this->formatDuration($todayStats['talk_time'] ?? 0) ?></h3>
                        <small><?= $this->__('agent.talk_time') ?></small>
                        <br><small class="opacity-75"><?= $this->__('agent.avg_duration') ?>: <?= $this->formatDuration(round($todayStats['avg_duration'] ?? 0)) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Calls -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-phone-alt me-2"></i><?= $this->__('agent.recent_calls') ?></span>
                <a href="/areports/reports/cdr?extension=<?= urlencode($extension) ?>" class="btn btn-sm btn-outline-primary">
                    <?= $this->__('agent.all_calls') ?>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('agent.time') ?></th>
                                <th><?= $this->__('agent.direction') ?></th>
                                <th><?= $this->__('agent.caller') ?></th>
                                <th><?= $this->__('agent.callee') ?></th>
                                <th><?= $this->__('agent.did') ?></th>
                                <th><?= $this->__('agent.duration') ?></th>
                                <th><?= $this->__('agent.status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCalls)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <?= $this->__('agent.no_calls_today') ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach (array_slice($recentCalls, 0, 15) as $call): ?>
                            <?php
                                // Determine direction: inbound if this extension is the destination/cnum
                                $isInbound = ($call['dst'] === $extension || $call['cnum'] === $extension)
                                             && $call['src'] !== $extension;
                                $direction = $isInbound ? 'inbound' : 'outbound';

                                // Caller info
                                $callerNum = $call['src'] ?: $call['cnum'] ?: '';
                                $callerName = $call['cnam'] ?: '';
                                $callerDisplay = $callerName ? "{$callerName} ({$callerNum})" : $callerNum;

                                // Callee info
                                $calleeNum = $call['dst'] ?: '';
                                $calleeName = $call['dst_cnam'] ?: '';
                                $calleeDisplay = $calleeName ? "{$calleeName} ({$calleeNum})" : $calleeNum;

                                // DID
                                $did = $call['did'] ?: '';
                            ?>
                            <tr>
                                <td><?= date('H:i:s', strtotime($call['calldate'])) ?></td>
                                <td>
                                    <?php if ($direction === 'inbound'): ?>
                                    <span class="badge bg-success"><i class="fas fa-arrow-down"></i> <?= $this->__('agent.inbound') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-primary"><i class="fas fa-arrow-up"></i> <?= $this->__('agent.outbound') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $this->e($callerDisplay) ?></td>
                                <td><?= $this->e($calleeDisplay) ?></td>
                                <td><?= $this->e($did) ?></td>
                                <td><?= $this->formatDuration($call['billsec']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($call['disposition']) {
                                        'ANSWERED' => 'success',
                                        'NO ANSWER' => 'warning',
                                        'BUSY' => 'info',
                                        default => 'danger'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $call['disposition'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Status Panel -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-headset me-2"></i><?= $this->__('agent.queue_status') ?></span>
                <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-status" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div id="queue-status-container">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted"><?= $this->__('agent.loading_status') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i><?= $this->__('agent.quick_actions') ?>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/areports/reports/agent/my" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar me-2"></i><?= $this->__('agent.my_report') ?>
                    </a>
                    <a href="/areports/quality/recordings?extension=<?= urlencode($extension) ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-microphone me-2"></i><?= $this->__('agent.my_recordings') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pause Reason Modal -->
<div class="modal fade" id="pauseModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $this->__('agent.select_pause_reason') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pause-queue" value="">
                <div class="list-group">
                    <?php foreach ($pauseCauses as $cause): ?>
                    <button type="button" class="list-group-item list-group-item-action pause-reason-btn"
                            data-reason="<?= $this->e($cause['code']) ?>">
                        <strong><?= $this->e($cause['name']) ?></strong>
                        <?php if ($cause['description']): ?>
                        <br><small class="text-muted"><?= $this->e($cause['description']) ?></small>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translation strings
    const __t = {
        not_in_queue: '<?= $this->__('agent.not_in_queue') ?>',
        paused: '<?= $this->__('agent.paused') ?>',
        pause_duration: '<?= $this->__('agent.pause_duration') ?>',
        available: '<?= $this->__('agent.available') ?>',
        busy: '<?= $this->__('agent.busy') ?>',
        ready: '<?= $this->__('agent.ready') ?>',
        waiting: '<?= $this->__('agent.waiting') ?>',
        queues_not_configured: '<?= $this->__('agent.queues_not_configured') ?>',
        loading_status: '<?= $this->__('agent.loading_status') ?>',
        error_loading: '<?= $this->__('agent.error_loading') ?>',
        confirm_logout_all: '<?= $this->__('agent.confirm_logout_all') ?>',
        action_failed: '<?= $this->__('agent.action_failed') ?>'
    };

    const statusContainer = document.getElementById('queue-status-container');
    const pauseModal = new bootstrap.Modal(document.getElementById('pauseModal'));
    let refreshInterval;
    let pauseTimerInterval = null;
    let serverTimeOffset = 0; // difference between server and client time

    // Format seconds to HH:MM:SS
    function formatPauseTime(seconds) {
        if (seconds <= 0) return '0:00';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        if (h > 0) {
            return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
        return `${m}:${String(s).padStart(2, '0')}`;
    }

    // Update all pause timers every second
    function startPauseTimers() {
        if (pauseTimerInterval) clearInterval(pauseTimerInterval);
        pauseTimerInterval = setInterval(() => {
            document.querySelectorAll('.pause-timer').forEach(el => {
                const pauseStart = parseInt(el.dataset.pauseStart);
                if (pauseStart > 0) {
                    const now = Math.floor(Date.now() / 1000) - serverTimeOffset;
                    const elapsed = now - pauseStart;
                    el.textContent = formatPauseTime(elapsed > 0 ? elapsed : 0);
                }
            });
        }, 1000);
    }

    // Load queue status
    function loadQueueStatus() {
        fetch('/areports/agent/status')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    statusContainer.innerHTML = `<div class="alert alert-danger m-3">${data.error}</div>`;
                    return;
                }
                // Calculate server time offset
                if (data.server_time) {
                    serverTimeOffset = Math.floor(Date.now() / 1000) - data.server_time;
                }
                renderQueueStatus(data.queues);
            })
            .catch(error => {
                statusContainer.innerHTML = `<div class="alert alert-danger m-3">${__t.error_loading}</div>`;
            });
    }

    // Render queue status
    function renderQueueStatus(queues) {
        if (!queues || queues.length === 0) {
            statusContainer.innerHTML = `<div class="text-center py-4 text-muted">${__t.queues_not_configured}</div>`;
            return;
        }

        let hasPaused = false;
        let html = '<ul class="list-group list-group-flush">';
        queues.forEach(queue => {
            let statusBadge = '';
            let statusIcon = '';

            if (!queue.in_queue) {
                statusBadge = `<span class="badge bg-secondary">${__t.not_in_queue}</span>`;
                statusIcon = 'fa-sign-out-alt text-secondary';
            } else if (queue.paused) {
                hasPaused = true;
                statusBadge = `<span class="badge bg-warning">${__t.paused}</span>`;
                if (queue.paused_reason) {
                    statusBadge += ` <small class="text-muted">(${queue.paused_reason})</small>`;
                }
                // Pause timer
                if (queue.last_pause && queue.last_pause > 0) {
                    statusBadge += ` <span class="badge bg-warning text-dark pause-timer" data-pause-start="${queue.last_pause}"><i class="fas fa-clock me-1"></i>...</span>`;
                }
                statusIcon = 'fa-pause text-warning';
            } else if (queue.status === 'available') {
                statusBadge = `<span class="badge bg-success">${__t.available}</span>`;
                statusIcon = 'fa-check-circle text-success';
            } else if (queue.status === 'busy' || queue.status === 'ringing' || queue.status === 'ringinuse') {
                statusBadge = `<span class="badge bg-danger">${__t.busy}</span>`;
                statusIcon = 'fa-phone text-danger';
            } else {
                statusBadge = `<span class="badge bg-info">${__t.ready}</span>`;
                statusIcon = 'fa-headset text-info';
            }

            html += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <i class="fas ${statusIcon} me-2"></i>
                            <strong>${queue.display_name || queue.queue}</strong>
                            <small class="text-muted">(${queue.queue})</small>
                            ${queue.calls_waiting > 0 ? `<span class="badge bg-danger ms-2">${queue.calls_waiting} ${__t.waiting}</span>` : ''}
                            <br>
                            <small>${statusBadge}</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            ${!queue.in_queue ? `
                                <button class="btn btn-outline-success queue-login-btn" data-queue="${queue.queue}" title="Login">
                                    <i class="fas fa-sign-in-alt"></i>
                                </button>
                            ` : `
                                <button class="btn btn-outline-danger queue-logout-btn" data-queue="${queue.queue}" title="Logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                                ${queue.paused ? `
                                    <button class="btn btn-outline-info queue-unpause-btn" data-queue="${queue.queue}" title="Unpause">
                                        <i class="fas fa-play"></i>
                                    </button>
                                ` : `
                                    <button class="btn btn-outline-warning queue-pause-btn" data-queue="${queue.queue}" title="Pause">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                `}
                            `}
                        </div>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        statusContainer.innerHTML = html;

        // Start pause timers if any agent is paused
        if (hasPaused) {
            startPauseTimers();
        } else if (pauseTimerInterval) {
            clearInterval(pauseTimerInterval);
            pauseTimerInterval = null;
        }

        // Attach event handlers
        attachQueueButtonHandlers();
    }

    // Attach handlers to queue buttons
    function attachQueueButtonHandlers() {
        document.querySelectorAll('.queue-login-btn').forEach(btn => {
            btn.addEventListener('click', () => queueAction('login', btn.dataset.queue));
        });
        document.querySelectorAll('.queue-logout-btn').forEach(btn => {
            btn.addEventListener('click', () => queueAction('logout', btn.dataset.queue));
        });
        document.querySelectorAll('.queue-pause-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('pause-queue').value = btn.dataset.queue;
                pauseModal.show();
            });
        });
        document.querySelectorAll('.queue-unpause-btn').forEach(btn => {
            btn.addEventListener('click', () => queueAction('unpause', btn.dataset.queue));
        });
    }

    // Queue action
    function queueAction(action, queue, reason = '') {
        const formData = new FormData();
        formData.append('queue', queue);
        if (reason) formData.append('reason', reason);

        fetch(`/areports/agent/queue-${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                toastr.error(data.error);
            } else {
                toastr.success(data.message);
                loadQueueStatus();
            }
        })
        .catch(() => toastr.error(__t.action_failed));
    }

    // Pause reason selection
    document.querySelectorAll('.pause-reason-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const queue = document.getElementById('pause-queue').value;
            const reason = btn.dataset.reason;
            pauseModal.hide();
            queueAction('pause', queue, reason);
        });
    });

    // Login all
    document.getElementById('btn-login-all').addEventListener('click', function() {
        this.disabled = true;
        fetch('/areports/agent/login-all', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    toastr.error(data.error);
                } else {
                    toastr.success(data.message);
                    loadQueueStatus();
                }
            })
            .finally(() => this.disabled = false);
    });

    // Logout all
    document.getElementById('btn-logout-all').addEventListener('click', function() {
        if (!confirm(__t.confirm_logout_all)) return;
        this.disabled = true;
        fetch('/areports/agent/logout-all', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    toastr.error(data.error);
                } else {
                    toastr.success(data.message);
                    loadQueueStatus();
                }
            })
            .finally(() => this.disabled = false);
    });

    // Pause all
    document.getElementById('btn-pause-all').addEventListener('click', function() {
        document.getElementById('pause-queue').value = '';
    });

    // Unpause all
    document.getElementById('btn-unpause-all').addEventListener('click', function() {
        this.disabled = true;
        queueAction('unpause', '');
        this.disabled = false;
    });

    // Refresh button
    document.getElementById('btn-refresh-status').addEventListener('click', loadQueueStatus);

    // Initial load and auto-refresh
    loadQueueStatus();
    refreshInterval = setInterval(loadQueueStatus, 10000); // Refresh every 10 seconds
});
</script>
<?php $this->endSection(); ?>
