<?php $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-body text-center py-5">
                <i class="fas fa-phone-slash fa-4x text-muted mb-4"></i>
                <h4><?= $this->__('agent.no_extension') ?></h4>
                <p class="text-muted">
                    <?= $this->__('agent.no_extension_desc') ?>
                </p>
                <a href="/areports/dashboard" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
