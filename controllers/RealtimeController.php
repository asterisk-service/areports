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
     * Get real-time active calls from channels
     * Simple approach: find trunk channels with Application=Queue or Dial
     * These channels contain all needed info (caller, agent, queue)
     */
    public function calls(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();
            $channels = $ami->getActiveChannels();

            $activeCalls = [];
            $seen = [];

            foreach ($channels as $ch) {
                $chanName = $ch['channel'] ?? '';
                $app = $ch['application'] ?? '';
                $stateDesc = $ch['state_desc'] ?? '';

                // Skip Local channels (intermediary queue channels)
                if (strpos($chanName, 'Local/') === 0) continue;

                // Skip non-call applications
                $appLower = strtolower($app);
                if (in_array($appLower, ['', 'playback', 'park', 'mixmonitor', 'read', 'wait', 'answer', 'appdial', 'appqueue'])) continue;

                $callerNum = $ch['caller_id_num'] ?? '';
                if (empty($callerNum)) continue;

                // Deduplicate by caller number + uniqueid
                $dedupKey = $callerNum . '_' . ($ch['uniqueid'] ?? '');
                if (isset($seen[$dedupKey])) continue;
                $seen[$dedupKey] = true;

                // Determine queue name and state
                $queueName = '';
                $state = strtolower($stateDesc ?: 'unknown');
                $agentExt = $ch['connected_line_num'] ?? '';
                $agentName = $ch['connected_line_name'] ?? '';

                if (strcasecmp($app, 'Queue') === 0) {
                    // Trunk channel in a queue
                    // ApplicationData format: "602,t,,custom/ElenaMiro1,3600,,,,,"
                    $parts = explode(',', $ch['application_data'] ?? '');
                    $queueName = $parts[0] ?? '';

                    if ($stateDesc === 'Up' && !empty($agentExt)) {
                        $state = 'talking';
                    } elseif ($stateDesc === 'Up') {
                        $state = 'waiting';
                    } else {
                        $state = 'ringing';
                    }
                } elseif (strcasecmp($app, 'Dial') === 0) {
                    // Outbound or direct call
                    $state = ($stateDesc === 'Up') ? 'talking' : 'ringing';
                }

                $activeCalls[] = [
                    'queue' => $queueName,
                    'caller_id' => $callerNum,
                    'caller_name' => $ch['caller_id_name'] ?? '',
                    'connected_to' => $agentExt,
                    'agent_name' => $agentName,
                    'state' => $state,
                    'duration' => $ch['duration'] ?? 0,
                    'wait' => 0,
                ];
            }

            $this->json([
                'success' => true,
                'data' => $activeCalls,
                'count' => count($activeCalls),
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
