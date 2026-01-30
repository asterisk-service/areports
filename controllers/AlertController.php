<?php
/**
 * Alert Controller
 * Manages alert configuration and history
 */

namespace aReports\Controllers;

use aReports\Core\Controller;
use aReports\Services\QueueService;

class AlertController extends Controller
{
    /**
     * List alerts
     */
    public function index(): void
    {
        $this->requirePermission('alerts.view');

        $alerts = $this->db->fetchAll(
            "SELECT a.*, u.first_name, u.last_name
             FROM alerts a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.is_active DESC, a.name"
        );

        $this->render('alerts/index', [
            'title' => 'Alerts',
            'currentPage' => 'alerts',
            'alerts' => $alerts
        ]);
    }

    /**
     * Alert history
     */
    public function history(): void
    {
        $this->requirePermission('alerts.view');

        $page = (int) $this->get('page', 1);
        $perPage = 50;

        $history = $this->db->fetchAll(
            "SELECT ah.*, a.name as alert_name
             FROM alert_history ah
             JOIN alerts a ON ah.alert_id = a.id
             ORDER BY ah.triggered_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, ($page - 1) * $perPage]
        );

        $total = $this->db->count('alert_history');

        $this->render('alerts/history', [
            'title' => 'Alert History',
            'currentPage' => 'alerts.history',
            'history' => $history,
            'page' => $page,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Create alert form
     */
    public function create(): void
    {
        $this->requirePermission('alerts.manage');

        $queueService = new QueueService();
        $queues = $queueService->getQueueList();

        $this->render('alerts/create', [
            'title' => 'Create Alert',
            'currentPage' => 'alerts',
            'queues' => $queues
        ]);
    }

    /**
     * Store alert
     */
    public function store(): void
    {
        $this->requirePermission('alerts.manage');

        $data = $this->validate($_POST, [
            'name' => 'required|max:100',
            'alert_type' => 'required|in:queue,agent,system',
            'metric' => 'required',
            'operator' => 'required|in:gt,lt,eq,gte,lte',
            'threshold_value' => 'required|numeric'
        ]);

        // Build notification channels and recipients
        $channels = [];
        $recipients = [];

        $notifyEmail = $this->post('notify_email');
        if (!empty($notifyEmail)) {
            $channels[] = 'email';
            $recipients['email'] = array_map('trim', explode(',', $notifyEmail));
        }

        $telegramChatId = $this->post('telegram_chat_id');
        if (!empty($telegramChatId)) {
            $channels[] = 'telegram';
            $recipients['telegram'] = array_map('trim', explode(',', $telegramChatId));
        }

        $queueId = $this->post('queue_id');

        $alertId = $this->db->insert('alerts', [
            'name' => $data['name'],
            'alert_type' => $data['alert_type'],
            'metric' => $data['metric'],
            'operator' => $data['operator'],
            'threshold_value' => $data['threshold_value'],
            'queue_id' => !empty($queueId) ? (int) $queueId : null,
            'cooldown_minutes' => (int) ($this->post('cooldown_minutes') ?: 15),
            'notification_channels' => json_encode($channels),
            'recipients' => json_encode($recipients),
            'is_active' => 1,
            'user_id' => $this->user['id']
        ]);

        $this->audit('create', 'alert', $alertId);
        $this->redirectWith('/areports/alerts', 'success', 'Alert created successfully.');
    }

    /**
     * Edit alert form
     */
    public function edit(int $id): void
    {
        $this->requirePermission('alerts.manage');

        $alert = $this->db->fetch("SELECT * FROM alerts WHERE id = ?", [$id]);
        if (!$alert) {
            $this->abort(404, 'Alert not found');
        }

        // Decode JSON fields
        $alert['notification_channels'] = json_decode($alert['notification_channels'] ?? '[]', true) ?: [];
        $alert['recipients'] = json_decode($alert['recipients'] ?? '{}', true) ?: [];

        $queueService = new QueueService();
        $queues = $queueService->getQueueList();

        $this->render('alerts/edit', [
            'title' => 'Edit Alert',
            'currentPage' => 'alerts',
            'alert' => $alert,
            'queues' => $queues
        ]);
    }

    /**
     * Update alert
     */
    public function update(int $id): void
    {
        $this->requirePermission('alerts.manage');

        $data = $this->validate($_POST, [
            'name' => 'required|max:100',
            'alert_type' => 'required|in:queue,agent,system',
            'metric' => 'required',
            'operator' => 'required|in:gt,lt,eq,gte,lte',
            'threshold_value' => 'required|numeric'
        ]);

        // Build notification channels and recipients
        $channels = [];
        $recipients = [];

        $notifyEmail = $this->post('notify_email');
        if (!empty($notifyEmail)) {
            $channels[] = 'email';
            $recipients['email'] = array_map('trim', explode(',', $notifyEmail));
        }

        $telegramChatId = $this->post('telegram_chat_id');
        if (!empty($telegramChatId)) {
            $channels[] = 'telegram';
            $recipients['telegram'] = array_map('trim', explode(',', $telegramChatId));
        }

        $queueId = $this->post('queue_id');

        $this->db->update('alerts', [
            'name' => $data['name'],
            'alert_type' => $data['alert_type'],
            'metric' => $data['metric'],
            'operator' => $data['operator'],
            'threshold_value' => $data['threshold_value'],
            'queue_id' => !empty($queueId) ? (int) $queueId : null,
            'cooldown_minutes' => (int) ($this->post('cooldown_minutes') ?: 15),
            'notification_channels' => json_encode($channels),
            'recipients' => json_encode($recipients),
            'is_active' => $this->post('is_active') ? 1 : 0
        ], ['id' => $id]);

        $this->audit('update', 'alert', $id);
        $this->redirectWith('/areports/alerts', 'success', 'Alert updated successfully.');
    }

    /**
     * Delete alert
     */
    public function delete(int $id): void
    {
        $this->requirePermission('alerts.manage');

        $this->db->delete('alerts', ['id' => $id]);
        $this->audit('delete', 'alert', $id);
        $this->redirectWith('/areports/alerts', 'success', 'Alert deleted successfully.');
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge(int $id): void
    {
        $this->requirePermission('alerts.view');

        $this->db->update('alert_history', [
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'acknowledged_by' => $this->user['id']
        ], ['id' => $id, 'acknowledged_at' => null]);

        $this->redirectWith('/areports/alerts/history', 'success', 'Alert acknowledged.');
    }
}
