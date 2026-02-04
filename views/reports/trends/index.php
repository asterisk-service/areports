<?php $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('reports.trend_reports') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('reports.trend_reports_subtitle') ?></p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="display-4 text-primary mb-3">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="card-title"><?= $this->__('reports.hourly_trends') ?></h5>
                <p class="card-text text-muted"><?= $this->__('reports.hourly_trends_desc') ?></p>
                <a href="/areports/reports/trends/hourly" class="btn btn-primary"><?= $this->__('reports.view_report') ?></a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="display-4 text-success mb-3">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="card-title"><?= $this->__('reports.daily_trends') ?></h5>
                <p class="card-text text-muted"><?= $this->__('reports.daily_trends_desc') ?></p>
                <a href="/areports/reports/trends/daily" class="btn btn-success"><?= $this->__('reports.view_report') ?></a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="display-4 text-warning mb-3">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h5 class="card-title"><?= $this->__('reports.period_comparison') ?></h5>
                <p class="card-text text-muted"><?= $this->__('reports.period_comparison_desc') ?></p>
                <a href="/areports/reports/trends/comparison" class="btn btn-warning"><?= $this->__('reports.view_report') ?></a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
