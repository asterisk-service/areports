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
     * Uses only CoreShowChannels (no extra QueueStatus call)
     * Queue callers (waiting) are rendered from queue data on the JS side
     */
    public function calls(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();
            $channels = $ami->getActiveChannels();

            // Group channels by bridge_id to pair caller<->agent
            $bridgeMap = [];
            $unbridged = [];
            foreach ($channels as $ch) {
                if (!empty($ch['bridge_id'])) {
                    $bridgeMap[$ch['bridge_id']][] = $ch;
                } else {
                    $unbridged[] = $ch;
                }
            }

            $activeCalls = [];
            $seenBridges = [];

            // Process bridged channels (connected calls)
            foreach ($bridgeMap as $bridgeId => $bridgeChannels) {
                if (count($bridgeChannels) < 2) continue;
                if (isset($seenBridges[$bridgeId])) continue;
                $seenBridges[$bridgeId] = true;

                // Separate channels: find external caller and internal agent
                // Strategy: channel with ConnectedLineNum that looks like short ext = caller side
                // Channel whose name contains PJSIP/ext or Local/ext@from-queue = agent side
                $callerCh = null;
                $agentCh = null;

                foreach ($bridgeChannels as $ch) {
                    $chanName = $ch['channel'] ?? '';
                    // Skip Local channels (intermediary queue channels)
                    if (strpos($chanName, 'Local/') === 0) continue;

                    // PJSIP/SIP with short extension number = agent phone
                    if (preg_match('/^(PJSIP|SIP)\/(\d{3,5})-/', $chanName)) {
                        $agentCh = $ch;
                    } else {
                        $callerCh = $ch;
                    }
                }

                // Fallback: if both are PJSIP, use connected_line_num to determine
                if (!$callerCh || !$agentCh) {
                    $nonLocal = array_filter($bridgeChannels, function($c) {
                        return strpos($c['channel'] ?? '', 'Local/') !== 0;
                    });
                    $nonLocal = array_values($nonLocal);
                    if (count($nonLocal) >= 2) {
                        $callerCh = $nonLocal[0];
                        $agentCh = $nonLocal[1];
                    } elseif (count($nonLocal) === 1) {
                        // Only one non-local channel, use it as caller
                        $callerCh = $nonLocal[0];
                        // Agent might be a Local channel
                        foreach ($bridgeChannels as $ch) {
                            if (strpos($ch['channel'] ?? '', 'Local/') === 0) {
                                $agentCh = $ch;
                                break;
                            }
                        }
                    }
                }

                if (!$callerCh) continue;

                // Extract caller info
                $callerNum = $callerCh['caller_id_num'] ?? '';
                $callerName = $callerCh['caller_id_name'] ?? '';

                // Extract agent info
                $agentExt = '';
                $agentName = '';
                if ($agentCh) {
                    // Try to get ext from channel name: PJSIP/200-xxx
                    if (preg_match('/(?:PJSIP|SIP)\/(\d+)/', $agentCh['channel'] ?? '', $m)) {
                        $agentExt = $m[1];
                    } elseif (preg_match('/Local\/(\d+)@/', $agentCh['channel'] ?? '', $m)) {
                        $agentExt = $m[1];
                    }
                    // Agent name from connected_line_name of caller channel
                    $agentName = $callerCh['connected_line_name'] ?? '';
                    if (empty($agentName)) {
                        $agentName = $agentCh['caller_id_name'] ?? '';
                    }
                    // If agent ext is empty, try connected_line_num from caller
                    if (empty($agentExt)) {
                        $agentExt = $callerCh['connected_line_num'] ?? '';
                    }
                }

                // Try to find queue name from application data
                $queueName = '';
                foreach ($bridgeChannels as $ch) {
                    $app = $ch['application'] ?? '';
                    if (stripos($app, 'Queue') !== false) {
                        $parts = explode(',', $ch['application_data'] ?? '');
                        $queueName = $parts[0] ?? '';
                        break;
                    }
                    // Also check context for queue hint
                    $ctx = $ch['context'] ?? '';
                    if (strpos($ctx, 'from-queue') !== false && preg_match('/Local\/\d+@from-queue/', $ch['channel'] ?? '')) {
                        // We know it's a queue call but don't know which queue
                    }
                }

                $activeCalls[] = [
                    'queue' => $queueName,
                    'caller_id' => $callerNum,
                    'caller_name' => $callerName,
                    'connected_to' => $agentExt,
                    'agent_name' => $agentName,
                    'state' => 'talking',
                    'duration' => $callerCh['duration'] ?? 0,
                    'wait' => 0,
                ];
            }

            // Process unbridged channels (ringing, waiting in queue, IVR)
            foreach ($unbridged as $ch) {
                $chanName = $ch['channel'] ?? '';
                // Skip Local and internal channels
                if (strpos($chanName, 'Local/') === 0) continue;

                $app = strtolower($ch['application'] ?? '');
                // Skip non-call applications
                if (in_array($app, ['', 'playback', 'park', 'mixmonitor', 'read', 'wait', 'answer'])) continue;

                $callerNum = $ch['caller_id_num'] ?? '';
                if (empty($callerNum)) continue;

                // Determine queue and state
                $queueName = '';
                $state = strtolower($ch['state_desc'] ?? 'unknown');
                if (stripos($app, 'queue') !== false) {
                    $parts = explode(',', $ch['application_data'] ?? '');
                    $queueName = $parts[0] ?? '';
                    $state = 'waiting';
                } elseif (stripos($app, 'dial') !== false) {
                    $state = 'ringing';
                }

                $activeCalls[] = [
                    'queue' => $queueName,
                    'caller_id' => $callerNum,
                    'caller_name' => $ch['caller_id_name'] ?? '',
                    'connected_to' => $ch['connected_line_num'] ?? '',
                    'agent_name' => $ch['connected_line_name'] ?? '',
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
