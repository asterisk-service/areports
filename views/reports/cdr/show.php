<?php $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('reports.call_details') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('reports.unique_id') ?>: <?= $this->e($cdr['uniqueid']) ?></p>
    </div>
    <a href="/areports/reports/cdr" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> <?= $this->__('reports.back_to_cdr') ?>
    </a>
</div>

<div class="row">
    <!-- Main Call Info -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= $this->__('reports.call_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%"><?= $this->__('reports.date_time') ?></th>
                                <td><?= $this->formatDateTime($cdr['calldate']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.caller_id') ?></th>
                                <td><?= $this->e($cdr['clid']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.source') ?></th>
                                <td><code><?= $this->e($cdr['src']) ?></code></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.destination') ?></th>
                                <td><code><?= $this->e($cdr['dst']) ?></code></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.did') ?></th>
                                <td><?= $this->e($cdr['did'] ?: '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%"><?= $this->__('reports.context') ?></th>
                                <td><code><?= $this->e($cdr['dcontext']) ?></code></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.duration') ?></th>
                                <td><?= $this->formatDuration((int)$cdr['duration']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.billable') ?></th>
                                <td><?= $this->formatDuration((int)$cdr['billsec']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.disposition') ?></th>
                                <td>
                                    <span class="badge bg-<?= $cdr['disposition'] === 'ANSWERED' ? 'success' : ($cdr['disposition'] === 'NO ANSWER' ? 'warning' : 'danger') ?>">
                                        <?= $this->e($cdr['disposition']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?= $this->__('reports.ama_flags') ?></th>
                                <td><?= $this->e($cdr['amaflags'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= $this->__('reports.channel_information') ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="20%"><?= $this->__('reports.source_channel') ?></th>
                        <td><code><?= $this->e($cdr['channel']) ?></code></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('reports.dst_channel') ?></th>
                        <td><code><?= $this->e($cdr['dstchannel'] ?: '-') ?></code></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('reports.last_application') ?></th>
                        <td><?= $this->e($cdr['lastapp'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('reports.last_data') ?></th>
                        <td><code><?= $this->e($cdr['lastdata'] ?? '-') ?></code></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('reports.unique_id') ?></th>
                        <td><code><?= $this->e($cdr['uniqueid']) ?></code></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('reports.linked_id') ?></th>
                        <td>
                            <?php if ($cdr['linkedid']): ?>
                            <a href="/areports/reports/cdr/call-flow/<?= $this->e($cdr['linkedid']) ?>">
                                <code><?= $this->e($cdr['linkedid']) ?></code>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Recording -->
        <?php if (!empty($cdr['recordingfile'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= $this->__('reports.recording') ?></h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong><?= $this->__('reports.file') ?>:</strong> <code><?= $this->e($cdr['recordingfile']) ?></code></p>
                <?php if ($this->can('reports.cdr.listen')): ?>
                <audio controls class="w-100" id="recording-player">
                    <source src="/areports/quality/recordings/<?= $this->e($cdr['uniqueid']) ?>/play" type="audio/wav">
                    <?= $this->__('reports.no_audio_support') ?>
                </audio>
                <div class="mt-2">
                    <a href="/areports/quality/recordings/<?= $this->e($cdr['uniqueid']) ?>/download" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> <?= $this->__('reports.download_recording') ?>
                    </a>
                </div>
                <?php else: ?>
                <p class="text-muted"><?= $this->__('reports.no_recording_permission') ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Related Calls -->
        <?php if (!empty($relatedCalls) && count($relatedCalls) > 1): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= $this->__('reports.related_calls') ?></h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($relatedCalls as $related): ?>
                    <a href="/areports/reports/cdr/<?= $this->e($related['uniqueid']) ?>"
                       class="list-group-item list-group-item-action <?= $related['uniqueid'] === $cdr['uniqueid'] ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <span><?= $this->e($related['src']) ?> &rarr; <?= $this->e($related['dst']) ?></span>
                            <span class="badge bg-<?= $related['disposition'] === 'ANSWERED' ? 'success' : 'secondary' ?>">
                                <?= $this->e($related['disposition']) ?>
                            </span>
                        </div>
                        <small class="text-muted"><?= $this->formatDateTime($related['calldate']) ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= $this->__('common.actions') ?></h5>
            </div>
            <div class="card-body">
                <?php if ($cdr['linkedid']): ?>
                <a href="/areports/reports/cdr/call-flow/<?= $this->e($cdr['linkedid']) ?>" class="btn btn-outline-info w-100 mb-2">
                    <i class="fas fa-project-diagram me-1"></i> <?= $this->__('reports.view_call_flow') ?>
                </a>
                <?php endif; ?>
                <a href="/areports/reports/cdr?src=<?= urlencode($cdr['src']) ?>" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-phone me-1"></i> <?= $this->__('reports.other_calls_from') ?> <?= $this->e($cdr['src']) ?>
                </a>
                <a href="/areports/reports/cdr?dst=<?= urlencode($cdr['dst']) ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-phone-alt me-1"></i> <?= $this->__('reports.other_calls_to') ?> <?= $this->e($cdr['dst']) ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
