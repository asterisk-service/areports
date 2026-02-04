<?php $this->section('content'); ?>

<h4 class="mb-4 text-center"><?= $this->__('auth.sign_in') ?></h4>

<form method="POST" action="/areports/login">
    <?= $this->csrf() ?>

    <div class="mb-3">
        <label for="username" class="form-label"><?= $this->__('auth.username') ?></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text"
                   class="form-control"
                   id="username"
                   name="username"
                   value="<?= $this->e($session->getFlash('old')['username'] ?? '') ?>"
                   placeholder="Enter username or email"
                   required
                   autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label"><?= $this->__('auth.password') ?></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password"
                   class="form-control"
                   id="password"
                   name="password"
                   placeholder="Enter password"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember"><?= $this->__('auth.remember_me') ?></label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt me-2"></i><?= $this->__('auth.sign_in') ?>
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="/areports/forgot-password" class="text-muted small"><?= $this->__('auth.forgot_password') ?></a>
    </div>
</form>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php $this->endSection(); ?>
