<!DOCTYPE html>
<html lang="<?= $currentLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->e($csrfToken) ?>">
    <title><?= $this->e($title ?? $this->__('menu.dashboard')) ?> - aReports</title>

    <!-- Bootstrap CSS -->
    <link href="/areports/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/areports/vendor/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="/areports/vendor/datatables/datatables.min.css" rel="stylesheet">
    <!-- DateRangePicker -->
    <link href="/areports/vendor/daterangepicker/daterangepicker.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="/areports/vendor/select2/select2.min.css" rel="stylesheet">
    <link href="/areports/vendor/select2/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Toastr -->
    <link href="/areports/vendor/toastr/toastr.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/areports/css/app.css" rel="stylesheet">

    <?php if ($this->hasSection('styles')): ?>
    <?php $this->yield('styles'); ?>
    <?php endif; ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <a href="/areports/<?= ($user['role_id'] ?? 0) == 3 ? 'agent' : 'dashboard' ?>" class="sidebar-brand">
                    <i class="fas fa-chart-line me-2"></i>
                    <span>aReports</span>
                </a>
            </div>

            <ul class="sidebar-nav">
                <!-- Dashboard (hidden for agents) -->
                <?php if (($user['role_id'] ?? 0) != 3): ?>
                <li class="sidebar-item">
                    <a href="/areports/dashboard" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span><?= $this->__('menu.dashboard') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Agent Panel -->
                <?php if ($this->can('agent.panel.view')): ?>
                <li class="sidebar-item">
                    <a href="/areports/agent" class="sidebar-link <?= ($currentPage ?? '') === 'agent.panel' ? 'active' : '' ?>">
                        <i class="fas fa-headset"></i>
                        <span><?= $this->__('menu.agent_panel') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (($user['role_id'] ?? 0) != 3): ?>
                <!-- Real-Time -->
                <?php if ($this->can('realtime.view')): ?>
                <li class="sidebar-item">
                    <a href="#realtime-submenu" class="sidebar-link" data-bs-toggle="collapse">
                        <i class="fas fa-broadcast-tower"></i>
                        <span><?= $this->__('menu.realtime') ?></span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul id="realtime-submenu" class="sidebar-submenu collapse">
                        <li><a href="/areports/realtime"><?= $this->__('menu.monitoring') ?></a></li>
                        <?php if ($this->can('realtime.queue_status')): ?>
                        <li><a href="/areports/wallboard"><?= $this->__('menu.wallboard') ?></a></li>
                        <?php endif; ?>
                        <?php if ($this->can('realtime.queue_status')): ?>
                        <li><a href="/areports/reports/queue"><?= $this->__('menu.queue_status') ?></a></li>
                        <?php endif; ?>
                        <?php if ($this->can('realtime.agent_status')): ?>
                        <li><a href="/areports/reports/agent"><?= $this->__('menu.agent_status') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Reports -->
                <?php if ($this->can('reports.cdr.view') || $this->can('reports.agent.view') || $this->can('reports.queue.view')): ?>
                <li class="sidebar-item">
                    <a href="#reports-submenu" class="sidebar-link" data-bs-toggle="collapse">
                        <i class="fas fa-chart-bar"></i>
                        <span><?= $this->__('menu.reports') ?></span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul id="reports-submenu" class="sidebar-submenu collapse">
                        <?php if ($this->can('reports.cdr.view')): ?>
                        <li><a href="/areports/reports/cdr"><?= $this->__('menu.cdr') ?></a></li>
                        <?php endif; ?>
                        <?php if ($this->can('reports.agent.view')): ?>
                        <li><a href="/areports/reports/agent/performance"><?= $this->__('menu.agents') ?></a></li>
                        <?php endif; ?>
                        <?php if ($this->can('reports.queue.view')): ?>
                        <li><a href="/areports/reports/queue/summary"><?= $this->__('menu.queue_summary') ?></a></li>
                        <li><a href="/areports/reports/queue/sla"><?= $this->__('menu.sla_report') ?></a></li>
                        <li><a href="/areports/reports/queue/abandonment"><?= $this->__('menu.abandoned_calls') ?></a></li>
                        <?php endif; ?>
                        <li><a href="/areports/reports/trends"><?= $this->__('menu.trends') ?></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Quality Monitoring -->
                <?php if ($this->can('quality.view') || $this->can('reports.cdr.listen')): ?>
                <li class="sidebar-item">
                    <a href="#quality-submenu" class="sidebar-link" data-bs-toggle="collapse">
                        <i class="fas fa-headphones"></i>
                        <span><?= $this->__('menu.quality') ?></span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul id="quality-submenu" class="sidebar-submenu collapse">
                        <?php if ($this->can('reports.cdr.listen')): ?>
                        <li><a href="/areports/quality/recordings"><?= $this->__('menu.recordings') ?></a></li>
                        <?php endif; ?>
                        <?php if ($this->can('quality.view')): ?>
                        <li><a href="/areports/quality/evaluations"><?= $this->__('menu.evaluations') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Alerts -->
                <?php if ($this->can('alerts.view')): ?>
                <li class="sidebar-item">
                    <a href="/areports/alerts" class="sidebar-link <?= ($currentPage ?? '') === 'alerts' ? 'active' : '' ?>">
                        <i class="fas fa-bell"></i>
                        <span><?= $this->__('menu.alerts') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Scheduled Reports -->
                <?php if ($this->can('reports.schedule')): ?>
                <li class="sidebar-item">
                    <a href="/areports/scheduled-reports" class="sidebar-link <?= ($currentPage ?? '') === 'scheduled-reports' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i>
                        <span><?= $this->__('menu.scheduled_reports') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Admin Section -->
                <?php if ($this->can('admin.users.view') || $this->can('admin.settings')): ?>
                <li class="sidebar-header"><?= $this->__('menu.administration') ?></li>

                <?php if ($this->can('admin.users.view')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/users" class="sidebar-link <?= ($currentPage ?? '') === 'admin.users' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span><?= $this->__('menu.users') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->can('admin.roles.view')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/roles" class="sidebar-link <?= ($currentPage ?? '') === 'admin.roles' ? 'active' : '' ?>">
                        <i class="fas fa-user-tag"></i>
                        <span><?= $this->__('menu.roles') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->can('admin.queues')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/queues" class="sidebar-link <?= ($currentPage ?? '') === 'admin.queues' ? 'active' : '' ?>">
                        <i class="fas fa-layer-group"></i>
                        <span><?= $this->__('menu.queue_settings') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->can('admin.pause_causes.view')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/pause-causes" class="sidebar-link <?= ($currentPage ?? '') === 'admin.pause_causes' ? 'active' : '' ?>">
                        <i class="fas fa-pause-circle"></i>
                        <span><?= $this->__('menu.pause_causes') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->can('admin.settings')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/settings" class="sidebar-link <?= ($currentPage ?? '') === 'admin.settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span><?= $this->__('menu.settings') ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->can('admin.audit')): ?>
                <li class="sidebar-item">
                    <a href="/areports/admin/audit-log" class="sidebar-link <?= ($currentPage ?? '') === 'admin.audit' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>
                        <span><?= $this->__('menu.audit_log') ?></span>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
                <?php endif; /* end agent role check */ ?>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand navbar-light bg-white border-bottom px-3">
                <button class="btn btn-link sidebar-toggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <!-- Language Switcher -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-globe me-1"></i>
                                <?= strtoupper($currentLocale) ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="/areports/language/en" class="dropdown-item <?= $currentLocale === 'en' ? 'active' : '' ?>">
                                    English
                                </a>
                                <a href="/areports/language/ru" class="dropdown-item <?= $currentLocale === 'ru' ? 'active' : '' ?>">
                                    Русский
                                </a>
                            </div>
                        </li>

                        <!-- Alerts Dropdown -->
                        <?php if ($this->can('alerts.view')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger alert-badge d-none">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end alert-dropdown">
                                <h6 class="dropdown-header"><?= $this->__('menu.notifications') ?></h6>
                                <div class="alert-list">
                                    <p class="text-muted text-center py-3 mb-0"><?= $this->__('menu.no_notifications') ?></p>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="/areports/alerts" class="dropdown-item text-center"><?= $this->__('menu.all_alerts') ?></a>
                            </div>
                        </li>
                        <?php endif; ?>

                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= $this->e($user['first_name'] ?? $this->__('menu.user')) ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header"><?= $this->e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h6>
                                <span class="dropdown-item-text text-muted small"><?= $this->e($user['role_display_name'] ?? '') ?></span>
                                <div class="dropdown-divider"></div>
                                <a href="/areports/profile" class="dropdown-item">
                                    <i class="fas fa-user me-2"></i><?= $this->__('menu.profile') ?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/areports/logout" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i><?= $this->__('menu.logout') ?>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="content">
                <div class="container-fluid p-4">
                    <!-- Flash Messages -->
                    <?php if ($session->hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $this->e($session->getFlash('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if ($session->hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $this->e($session->getFlash('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if ($session->hasFlash('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $this->e($session->getFlash('warning')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Main Content -->
                    <?php $this->yield('content'); ?>
                </div>
            </main>

            <!-- Footer -->
            <footer class="footer bg-white border-top py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">&copy; <?= date('Y') ?> aReports v1.0.0</span>
                    <span class="text-muted small" id="server-time"></span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/areports/vendor/jquery/jquery.min.js"></script>
    <script src="/areports/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/areports/vendor/chartjs/chart.min.js"></script>
    <script src="/areports/vendor/datatables/datatables.min.js"></script>
    <script src="/areports/vendor/daterangepicker/moment.min.js"></script>
    <script src="/areports/vendor/daterangepicker/daterangepicker.min.js"></script>
    <script src="/areports/vendor/select2/select2.min.js"></script>
    <script src="/areports/vendor/toastr/toastr.min.js"></script>
    <script src="/areports/js/app.js"></script>

    <?php if ($this->hasSection('scripts')): ?>
    <?php $this->yield('scripts'); ?>
    <?php endif; ?>
</body>
</html>
