<?php $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $this->__('alerts.create_title') ?></h1>
    <a href="/areports/alerts" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?= $this->__('common.back') ?>
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/areports/alerts">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('alerts.alert_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="100"
                               placeholder="e.g., High Wait Time Alert">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= $this->__('alerts.alert_type') ?> <span class="text-danger">*</span></label>
                            <select name="alert_type" class="form-select" required>
                                <option value="queue">Queue</option>
                                <option value="agent">Agent</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?= $this->__('alerts.queue_filter') ?></label>
                            <select name="queue_id" class="form-select">
                                <option value=""><?= $this->__('alerts.all_queues') ?></option>
                                <?php foreach ($queues as $queue): ?>
                                <option value="<?= $this->e($queue['name']) ?>">
                                    <?= $this->e($queue['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><?= $this->__('alerts.metric') ?> <span class="text-danger">*</span></label>
                            <select name="metric" class="form-select" required>
                                <option value="">Select metric...</option>
                                <optgroup label="Queue Metrics">
                                    <option value="calls_waiting">Calls Waiting</option>
                                    <option value="longest_wait">Longest Wait Time</option>
                                    <option value="abandoned_rate">Abandon Rate %</option>
                                    <option value="sla_percentage">SLA %</option>
                                    <option value="avg_wait_time">Average Wait Time</option>
                                </optgroup>
                                <optgroup label="Agent Metrics">
                                    <option value="agents_available">Agents Available</option>
                                    <option value="agents_busy">Agents Busy</option>
                                    <option value="agents_paused">Agents Paused</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><?= $this->__('alerts.condition') ?> <span class="text-danger">*</span></label>
                            <select name="operator" class="form-select" required>
                                <option value="gt">Greater than (&gt;)</option>
                                <option value="gte">Greater than or equal (&gt;=)</option>
                                <option value="lt">Less than (&lt;)</option>
                                <option value="lte">Less than or equal (&lt;=)</option>
                                <option value="eq">Equals (=)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><?= $this->__('alerts.threshold') ?> <span class="text-danger">*</span></label>
                            <input type="number" name="threshold_value" class="form-control" required step="0.01"
                                   placeholder="e.g., 60">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('alerts.cooldown') ?></label>
                        <input type="number" name="cooldown_minutes" class="form-control" value="15" min="1" max="1440">
                        <small class="text-muted"><?= $this->__('alerts.cooldown_help') ?></small>
                    </div>

                    <hr>
                    <h6 class="mb-3"><?= $this->__('alerts.notification_channels') ?></h6>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-1"></i> <?= $this->__('alerts.email_recipients') ?></label>
                        <input type="text" name="notify_email" class="form-control"
                               placeholder="email@example.com (comma-separated for multiple)">
                        <small class="text-muted"><?= $this->__('alerts.email_help') ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-telegram me-1"></i> <?= $this->__('alerts.telegram_chat') ?></label>
                        <input type="text" name="telegram_chat_id" class="form-control"
                               placeholder="123456789 (comma-separated for multiple)">
                        <small class="text-muted"><?= $this->__('alerts.telegram_help') ?></small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/areports/alerts" class="btn btn-secondary"><?= $this->__('common.cancel') ?></a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?= $this->__('alerts.create_title') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Alerts</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Alerts monitor your call center metrics in real-time and notify you when thresholds are exceeded.</p>
                <ul class="small text-muted">
                    <li>Alerts are checked every minute</li>
                    <li>Notifications via Email and/or Telegram</li>
                    <li>Cooldown prevents alert spam</li>
                    <li>Configure Telegram bot in Admin &gt; Settings &gt; Telegram</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
