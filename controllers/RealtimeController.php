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
     * Combines queue callers (waiting) with active channels (connected)
     */
    public function calls(): void
    {
        $this->requirePermission('realtime.view');

        try {
            $ami = new AMIService();

            // 1. Get queue status - includes callers waiting in queues
            $queueStatus = $ami->getQueueStatus();

            // 2. Get active channels for connected calls
            $channels = $ami->getActiveChannels();

            // Build a map of active channels by caller number for matching
            // Group channels by bridge to pair caller<->agent
            $bridgeMap = [];
            foreach ($channels as $ch) {
                if (!empty($ch['bridge_id'])) {
                    $bridgeMap[$ch['bridge_id']][] = $ch;
                }
            }

            $activeCalls = [];

            // 3. Add callers waiting in queues
            foreach ($queueStatus as $queue) {
                $queueName = $queue['name'] ?? '';
                if (!empty($queue['callers'])) {
                    foreach ($queue['callers'] as $caller) {
                        $activeCalls[] = [
                            'queue' => $queueName,
                            'caller_id' => $caller['caller_id_num'] ?: '',
                            'caller_name' => $caller['caller_id_name'] ?: '',
                            'connected_to' => '',
                            'agent_name' => '',
                            'state' => 'waiting',
                            'duration' => 0,
                            'wait' => $caller['wait'] ?? 0,
                        ];
                    }
                }
            }

            // 4. Find connected calls - pair channels via bridge
            $seenCallers = [];
            foreach ($bridgeMap as $bridgeId => $bridgeChannels) {
                if (count($bridgeChannels) < 2) continue;

                // Find the external (caller) and internal (agent) channels
                $callerCh = null;
                $agentCh = null;
                foreach ($bridgeChannels as $ch) {
                    $chanName = $ch['channel'] ?? '';
                    // Queue local channels and PJSIP/SIP channels to agents
                    if (preg_match('/^Local\/\d+@from-queue/', $chanName) ||
                        preg_match('/^(PJSIP|SIP)\/\d+/', $chanName)) {
                        // This could be agent side
                        if ($agentCh === null) {
                            $agentCh = $ch;
                        }
                    } else {
                        // External/trunk channel = caller
                        if ($callerCh === null) {
                            $callerCh = $ch;
                        }
                    }
                }

                // If we couldn't determine which is which, use first two
                if (!$callerCh && !$agentCh && count($bridgeChannels) >= 2) {
                    $callerCh = $bridgeChannels[0];
                    $agentCh = $bridgeChannels[1];
                } elseif (!$callerCh) {
                    $callerCh = $bridgeChannels[0];
                } elseif (!$agentCh) {
                    $agentCh = $bridgeChannels[count($bridgeChannels) - 1];
                }

                $callerNum = $callerCh['caller_id_num'] ?? '';
                if (isset($seenCallers[$callerNum . '_' . $bridgeId])) continue;
                $seenCallers[$callerNum . '_' . $bridgeId] = true;

                // Extract agent extension from channel name
                $agentExt = '';
                $agentName = '';
                if ($agentCh) {
                    if (preg_match('/(?:PJSIP|SIP|Local)\/(\d+)/', $agentCh['channel'] ?? '', $m)) {
                        $agentExt = $m[1];
                    }
                    $agentName = $agentCh['connected_line_name'] ?? '';
                }

                // Determine queue from channel context or application data
                $queueName = '';
                foreach ($bridgeChannels as $ch) {
                    $appData = $ch['application_data'] ?? '';
                    if (stripos($ch['application'] ?? '', 'Queue') !== false && $appData) {
                        // Queue application data format: "queuename,options..."
                        $parts = explode(',', $appData);
                        $queueName = $parts[0] ?? '';
                        break;
                    }
                }

                $activeCalls[] = [
                    'queue' => $queueName,
                    'caller_id' => $callerNum,
                    'caller_name' => $callerCh['caller_id_name'] ?? '',
                    'connected_to' => $agentExt,
                    'agent_name' => $agentName,
                    'state' => 'talking',
                    'duration' => max($callerCh['duration'] ?? 0, $agentCh['duration'] ?? 0),
                    'wait' => 0,
                ];
            }

            // 5. Add unbridged channels that look like calls (ringing, etc.)
            foreach ($channels as $ch) {
                if (!empty($ch['bridge_id'])) continue; // already processed
                $app = strtolower($ch['application'] ?? '');
                // Skip internal channels, playback, parking, etc.
                if (in_array($app, ['', 'playback', 'park', 'mixmonitor', 'read', 'wait'])) continue;
                if (strpos($ch['channel'] ?? '', 'Local/') === 0) continue;

                $callerNum = $ch['caller_id_num'] ?? '';
                if (empty($callerNum)) continue;

                $queueName = '';
                if (stripos($app, 'queue') !== false) {
                    $parts = explode(',', $ch['application_data'] ?? '');
                    $queueName = $parts[0] ?? '';
                }

                $activeCalls[] = [
                    'queue' => $queueName,
                    'caller_id' => $callerNum,
                    'caller_name' => $ch['caller_id_name'] ?? '',
                    'connected_to' => $ch['connected_line_num'] ?? '',
                    'agent_name' => $ch['connected_line_name'] ?? '',
                    'state' => strtolower($ch['state_desc'] ?? 'unknown'),
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
