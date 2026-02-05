<?php
/**
 * Realtime Controller
 * Provides real-time data via AJAX API endpoints and realtime panel view
 */

namespace aReports\Controllers;

use aReports\Core\Controller;
use aReports\Services\AMIService;
use aReports\Services\QueueService;
use aReports\Services\AgentService;
use aReports\Services\CDRService;

class RealtimeController extends Controller
{
    /**
     * Display the realtime panel
     */
    public function index(): void
    {
        $this->requirePermission('realtime.view');

        // Get queue settings for the filter dropdown
        $queues = $this->db->fetchAll(
            "SELECT * FROM queue_settings WHERE is_monitored = 1 ORDER BY display_name ASC"
        );

        $this->render('realtime/panel', [
            'title' => 'Realtime Panel',
            'currentPage' => 'realtime',
            'queues' => $queues ?: []
        ]);
    }

    /**
     * Get real-time queue status
     */
    public function queues(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();
            $queues = $ami->getQueueStatus();

            $this->json([
                'success' => true,
                'data' => $queues,
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Failed to connect to AMI',
                'timestamp' => time()
            ]);
        }
    }

    /**
     * Get real-time agent status
     */
    public function agents(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();
            $queues = $ami->getQueueStatus();

            // Extract all agents from all queues
            $agents = [];
            foreach ($queues as $queue) {
                foreach ($queue['members'] as $member) {
                    $interface = $member['interface'];
                    if (!isset($agents[$interface])) {
                        $agents[$interface] = [
                            'interface' => $interface,
                            'name' => $member['name'],
                            'status' => $member['status'],
                            'status_text' => $member['status_text'],
                            'paused' => $member['paused'],
                            'paused_reason' => $member['paused_reason'],
                            'in_call' => $member['in_call'],
                            'calls_taken' => $member['calls_taken'],
                            'last_call' => $member['last_call'],
                            'queues' => []
                        ];
                    }
                    $agents[$interface]['queues'][] = $queue['name'];
                }
            }

            $this->json([
                'success' => true,
                'data' => array_values($agents),
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Failed to connect to AMI',
                'timestamp' => time()
            ]);
        }
    }

    /**
     * Get real-time active calls
     */
    public function calls(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();
            $channels = $ami->getActiveChannels();

            // Deduplicate calls - group by linkedid or uniqueid
            // For ring-all queues, multiple channels exist for the same call
            $uniqueCalls = [];
            foreach ($channels as $channel) {
                // Use linkedid if available, otherwise use caller_id as key
                $callKey = !empty($channel['linkedid'])
                    ? $channel['linkedid']
                    : ($channel['caller_id_num'] . '_' . floor($channel['duration'] / 10));

                // Keep the first (or most relevant) channel per call
                // Prefer channels that are connected (have connected_line_num)
                if (!isset($uniqueCalls[$callKey])) {
                    $uniqueCalls[$callKey] = [
                        'channel' => $channel['channel'],
                        'caller_id' => $channel['caller_id_num'],
                        'caller_name' => $channel['caller_id_name'],
                        'connected_to' => $channel['connected_line_num'],
                        'state' => $channel['state_desc'],
                        'duration' => $channel['duration'],
                        'application' => $channel['application'],
                        'context' => $channel['context'],
                        'extension' => $channel['extension'],
                        'linkedid' => $channel['linkedid'] ?? null
                    ];
                } elseif (!empty($channel['connected_line_num']) && empty($uniqueCalls[$callKey]['connected_to'])) {
                    // Update if this channel has connection info and previous didn't
                    $uniqueCalls[$callKey]['connected_to'] = $channel['connected_line_num'];
                    $uniqueCalls[$callKey]['state'] = $channel['state_desc'];
                }
            }

            $this->json([
                'success' => true,
                'data' => array_values($uniqueCalls),
                'count' => count($uniqueCalls),
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Failed to connect to AMI',
                'timestamp' => time()
            ]);
        }
    }

    /**
     * Get today's statistics
     */
    public function stats(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $cdrService = new CDRService();
            $queueService = new QueueService();

            $cdrStats = $cdrService->getTodayStats();
            $queueStats = $queueService->getTodayTotals();

            $this->json([
                'success' => true,
                'data' => [
                    'cdr' => $cdrStats,
                    'queue' => $queueStats
                ],
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ]);
        }
    }
}
