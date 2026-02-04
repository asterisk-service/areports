<!DOCTYPE html>
<html lang="<?= $currentLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->e($csrfToken) ?>">
    <title><?= $this->e($title ?? $this->__('auth.login')) ?> - aReports</title>

    <!-- Bootstrap CSS -->
    <link href="/areports/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/areports/vendor/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/areports/css/auth.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <!-- Language Switcher -->
                <div class="text-end mb-2">
                    <a href="/areports/language/en" class="btn btn-sm <?= $currentLocale === 'en' ? 'btn-primary' : 'btn-outline-secondary' ?>">EN</a>
                    <a href="/areports/language/ru" class="btn btn-sm <?= $currentLocale === 'ru' ? 'btn-primary' : 'btn-outline-secondary' ?>">RU</a>
                </div>

                <!-- Logo -->
                <div class="text-center mb-4">
                    <h1 class="h2 text-primary">
                        <i class="fas fa-chart-line me-2"></i>aReports
                    </h1>
                    <p class="text-muted"><?= $this->__('auth.subtitle') ?></p>
                </div>

                <!-- Flash Messages -->
                <?php if ($session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->e($session->getFlash('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($session->hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->e($session->getFlash('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Content -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php $this->yield('content'); ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4 text-muted small">
                    <p>&copy; <?= date('Y') ?> aReports. <?= $this->__('common.all_rights') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="/areports/vendor/jquery/jquery.min.js"></script>
    <script src="/areports/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
