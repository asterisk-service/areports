<?php
/**
 * Wallboard Layouts List View
 */
$layouts = $layouts ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $this->__('admin.wallboard_layouts') ?></h1>
        <a href="/areports/admin/wallboard-layouts/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i><?= $this->__('admin.create_layout') ?>
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($layouts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tv fa-3x text-muted mb-3"></i>
                <p class="text-muted">No wallboard layouts configured</p>
                <a href="/areports/admin/wallboard-layouts/create" class="btn btn-primary">
                    Create Your First Layout
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= $this->__('admin.layout_name') ?></th>
                            <th><?= $this->__('common.description') ?></th>
                            <th>Widgets</th>
                            <th><?= $this->__('common.status') ?></th>
                            <th><?= $this->__('common.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($layouts as $layout): ?>
                        <?php $widgets = json_decode($layout['widgets'] ?? '[]', true); ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($layout['name']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($layout['description'] ?? '-') ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= count($widgets) ?> widgets</span>
                            </td>
                            <td>
                                <?php if ($layout['is_active'] ?? 0): ?>
                                <span class="badge bg-success"><?= $this->__('common.active') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= $this->__('common.inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/areports/wallboard?layout=<?= $layout['id'] ?>" class="btn btn-outline-primary" title="Preview" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/areports/admin/wallboard-layouts/<?= $layout['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="deleteLayout(<?= $layout['id'] ?>, '<?= htmlspecialchars($layout['name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Layout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<strong id="deleteLayoutName"></strong>"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteLayout(id, name) {
    document.getElementById('deleteLayoutName').textContent = name;
    document.getElementById('deleteForm').action = '/areports/admin/wallboard-layouts/' + id + '/delete';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
