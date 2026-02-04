<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $this->__('admin.users') ?></h1>
        <p class="text-muted mb-0"><?= $this->__('admin.users_subtitle') ?></p>
    </div>
    <?php if ($this->can('admin.users.manage')): ?>
    <a href="/areports/admin/users/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> <?= $this->__('admin.create_user') ?>
    </a>
    <?php endif; ?>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= $this->__('common.name') ?></th>
                        <th><?= $this->__('admin.username') ?></th>
                        <th><?= $this->__('admin.email') ?></th>
                        <th><?= $this->__('admin.role') ?></th>
                        <th><?= $this->__('admin.extension') ?></th>
                        <th><?= $this->__('common.status') ?></th>
                        <th><?= $this->__('admin.last_login') ?></th>
                        <th><?= $this->__('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= $this->e($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                        </td>
                        <td><?= $this->e($u['username']) ?></td>
                        <td><?= $this->e($u['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $u['role_id'] === 1 ? 'danger' : ($u['role_id'] === 2 ? 'warning' : 'info') ?>">
                                <?= $this->e($u['role_name']) ?>
                            </span>
                        </td>
                        <td><?= $this->e($u['extension'] ?: '-') ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                            <span class="badge bg-success"><?= $this->__('common.active') ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?= $this->__('common.inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $u['last_login'] ? $this->formatDateTime($u['last_login']) : $this->__('common.never') ?>
                        </td>
                        <td>
                            <?php if ($this->can('admin.users.manage')): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="/areports/admin/users/<?= $u['id'] ?>/edit" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($u['id'] !== $user['id']): ?>
                                <form action="/areports/admin/users/<?= $u['id'] ?>/toggle" method="POST" class="d-inline">
                                    <?= $this->csrf() ?>
                                    <button type="submit" class="btn btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>"
                                            title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                </form>
                                <form action="/areports/admin/users/<?= $u['id'] ?>/delete" method="POST" class="d-inline"
                                      onsubmit="return confirm('<?= $this->__('admin.confirm_delete_user') ?>')">
                                    <?= $this->csrf() ?>
                                    <button type="submit" class="btn btn-outline-danger" title="<?= $this->__('common.delete') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
