<?php $this->section('content'); ?>
<?php
$alert = $alert ?? [];
$recipients = $alert['recipients'] ?? [];
$channels = $alert['notification_channels'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Alert</h1>
    <a href="/areports/alerts" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/areports/alerts/<?= $alert['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Alert Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="100"
                               value="<?= $this->e($alert['name'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alert Type <span class="text-danger">*</span></label>
                            <select name="alert_type" class="form-select" required>
                                <option value="queue" <?= ($alert['alert_type'] ?? '') === 'queue' ? 'selected' : '' ?>>Queue</option>
                                <option value="agent" <?= ($alert['alert_type'] ?? '') === 'agent' ? 'selected' : '' ?>>Agent</option>
                                <option value="system" <?= ($alert['alert_type'] ?? '') === 'system' ? 'selected' : '' ?>>System</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Queue Filter</label>
                            <select name="queue_id" class="form-select">
                                <option value="">All Queues</option>
                                <?php foreach ($queues as $queue): ?>
                                <option value="<?= $this->e($queue['name']) ?>"
                                    <?= ($alert['queue_id'] ?? '') == $queue['name'] ? 'selected' : '' ?>>
                                    <?= $this->e($queue['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Metric <span class="text-danger">*</span></label>
                            <select name="metric" class="form-select" required>
                                <option value="">Select metric...</option>
                                <optgroup label="Queue Metrics">
                                    <option value="calls_waiting" <?= ($alert['metric'] ?? '') === 'calls_waiting' ? 'selected' : '' ?>>Calls Waiting</option>
                                    <option value="longest_wait" <?= ($alert['metric'] ?? '') === 'longest_wait' ? 'selected' : '' ?>>Longest Wait Time</option>
                                    <option value="abandoned_rate" <?= ($alert['metric'] ?? '') === 'abandoned_rate' ? 'selected' : '' ?>>Abandon Rate %</option>
                                    <option value="sla_percentage" <?= ($alert['metric'] ?? '') === 'sla_percentage' ? 'selected' : '' ?>>SLA %</option>
                                    <option value="avg_wait_time" <?= ($alert['metric'] ?? '') === 'avg_wait_time' ? 'selected' : '' ?>>Average Wait Time</option>
                                </optgroup>
                                <optgroup label="Agent Metrics">
                                    <option value="agents_available" <?= ($alert['metric'] ?? '') === 'agents_available' ? 'selected' : '' ?>>Agents Available</option>
                                    <option value="agents_busy" <?= ($alert['metric'] ?? '') === 'agents_busy' ? 'selected' : '' ?>>Agents Busy</option>
                                    <option value="agents_paused" <?= ($alert['metric'] ?? '') === 'agents_paused' ? 'selected' : '' ?>>Agents Paused</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Condition <span class="text-danger">*</span></label>
                            <select name="operator" class="form-select" required>
                                <option value="gt" <?= ($alert['operator'] ?? '') === 'gt' ? 'selected' : '' ?>>Greater than (&gt;)</option>
                                <option value="gte" <?= ($alert['operator'] ?? '') === 'gte' ? 'selected' : '' ?>>Greater than or equal (&gt;=)</option>
                                <option value="lt" <?= ($alert['operator'] ?? '') === 'lt' ? 'selected' : '' ?>>Less than (&lt;)</option>
                                <option value="lte" <?= ($alert['operator'] ?? '') === 'lte' ? 'selected' : '' ?>>Less than or equal (&lt;=)</option>
                                <option value="eq" <?= ($alert['operator'] ?? '') === 'eq' ? 'selected' : '' ?>>Equals (=)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Threshold <span class="text-danger">*</span></label>
                            <input type="number" name="threshold_value" class="form-control" required step="0.01"
                                   value="<?= $this->e($alert['threshold_value'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cooldown (minutes)</label>
                            <input type="number" name="cooldown_minutes" class="form-control"
                                   value="<?= $this->e($alert['cooldown_minutes'] ?? 15) ?>" min="1" max="1440">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       <?= ($alert['is_active'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Notification Channels</h6>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-1"></i> Email</label>
                        <input type="text" name="notify_email" class="form-control"
                               value="<?= $this->e(implode(', ', $recipients['email'] ?? [])) ?>"
                               placeholder="email@example.com (comma-separated for multiple)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-telegram me-1"></i> Telegram Chat ID</label>
                        <input type="text" name="telegram_chat_id" class="form-control"
                               value="<?= $this->e(implode(', ', $recipients['telegram'] ?? [])) ?>"
                               placeholder="123456789 (comma-separated for multiple)">
                        <small class="text-muted">Configure bot token in Admin &gt; Settings &gt; Telegram.</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/areports/alerts" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Alert
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
