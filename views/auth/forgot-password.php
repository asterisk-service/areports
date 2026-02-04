<?php $this->section('content'); ?>

<h4 class="mb-4 text-center"><?= $this->__('auth.forgot_password_title') ?></h4>

<p class="text-muted text-center mb-4">
    <?= $this->__('auth.enter_email') ?>
</p>

<form method="POST" action="/areports/forgot-password">
    <?= $this->csrf() ?>

    <div class="mb-3">
        <label for="email" class="form-label"><?= $this->__('auth.email') ?></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email"
                   class="form-control"
                   id="email"
                   name="email"
                   placeholder="Enter your email"
                   required
                   autofocus>
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane me-2"></i><?= $this->__('auth.send_reset_link') ?>
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="/areports/login" class="text-muted small">
            <i class="fas fa-arrow-left me-1"></i><?= $this->__('auth.back_to_login') ?>
        </a>
    </div>
</form>

<?php $this->endSection(); ?>
