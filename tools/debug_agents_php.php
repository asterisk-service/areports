#!/usr/bin/env php
<?php
/**
 * Debug script: test agent name resolution on remote server
 * Run: php /var/www/html/areports/tools/debug_agents_php.php
 */

// Bootstrap the app
chdir('/var/www/html/areports');
require_once 'config/app.php';

use aReports\Services\AMIService;
use aReports\Core\App;

echo "=== PHP Agent Debug ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Test AMI connection and get queue data
echo "=== 1. AMI QueueStatus via AMIService ===\n";
try {
    $ami = new AMIService();
    $queues = $ami->getQueueStatus();
    echo "Queues returned: " . count($queues) . "\n\n";

    // Show members for queues 505, 601, 602, 603
    $targetQueues = ['505', '601', '602', '603', '606', '511', '530'];
    foreach ($queues as $queue) {
        if (!in_array($queue['name'], $targetQueues)) continue;
        echo "Queue: {$queue['name']}\n";
        echo "  Members: " . count($queue['members']) . "\n";
        foreach ($queue['members'] as $member) {
            echo "    interface: {$member['interface']}\n";
            echo "    name:      {$member['name']}\n";
            echo "    status:    {$member['status']} ({$member['status_text']})\n";
            echo "    paused:    " . ($member['paused'] ? 'yes' : 'no') . "\n";
            echo "    ---\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "AMI ERROR: " . $e->getMessage() . "\n";
}

// 2. Test agent name resolution (same logic as RealtimeController::data())
echo "=== 2. Agent Name Resolution ===\n";
$app = App::getInstance();
$db = $app->getDb();

// Load agent_settings
$agentRows = $db->fetchAll("SELECT extension, display_name FROM agent_settings WHERE display_name != ''");
echo "agent_settings rows: " . count($agentRows ?: []) . "\n";
$agentNames = [];
foreach ($agentRows ?: [] as $row) {
    $agentNames[$row['extension']] = $row['display_name'];
}
echo "agentNames map: " . json_encode($agentNames) . "\n\n";

// Process agents exactly like RealtimeController::data()
echo "=== 3. Agent Processing (same as data() endpoint) ===\n";
$agents = [];
foreach ($queues as $queue) {
    foreach ($queue['members'] as $member) {
        $interface = $member['interface'];
        if (!isset($agents[$interface])) {
            $name = $member['name'];
            $ext = '';
            if (preg_match('/(?:SIP|PJSIP|Local)\/(\d+)/i', $interface, $m)) {
                $ext = $m[1];
            }
            // Same logic as controller
            if ($name === $interface || $name === '<unknown>' || empty($name)) {
                $name = $agentNames[$ext] ?? '';
            }

            $agents[$interface] = [
                'interface' => $interface,
                'name' => $name,
                'ext' => $ext,
            ];
        }
    }
}

// Show results for our target agents
echo "Processed agents:\n";
foreach ($agents as $agent) {
    $ext = $agent['ext'];
    if (in_array($ext, ['7339', '7359', '7378', '7344', '7340', '7348', '7349', '7352', '7357', '7370', '7376', '7380'])) {
        echo "  interface: {$agent['interface']}\n";
        echo "  ext:       {$agent['ext']}\n";
        echo "  name:      " . ($agent['name'] ?: '(empty)') . "\n";
        echo "  display:   " . ($agent['name'] ? "{$agent['name']} ({$agent['ext']})" : $agent['ext']) . "\n";
        echo "  ---\n";
    }
}
echo "\n";

// 4. Test JS extractExtension logic in PHP
echo "=== 4. Extension Extraction Test ===\n";
$testInterfaces = [
    'Local/7339@from-queue/n',
    'Local/7378@from-queue/n',
    'Local/7359@from-queue/n',
    'Local/50010@from-queue/n',
    '<unknown>',
    '',
];
foreach ($testInterfaces as $iface) {
    $ext = '-';
    if (!empty($iface) && $iface !== '<unknown>') {
        if (preg_match('/local\/(\d+)@/i', $iface, $m)) {
            $ext = $m[1];
        } elseif (preg_match('/(?:PJSIP|SIP)\/(\d+)/i', $iface, $m)) {
            $ext = $m[1];
        }
    }
    echo "  '$iface' => '$ext'\n";
}
echo "\n";

// 5. Get active channels to check ConnectedLine fields
echo "=== 5. Active Channels (ConnectedLine fields) ===\n";
try {
    $ami2 = new AMIService();
    $channels = $ami2->getActiveChannels();
    echo "Active channels: " . count($channels) . "\n";
    foreach ($channels as $ch) {
        $chanName = $ch['channel'] ?? '';
        // Skip Local channels
        if (strpos($chanName, 'Local/') === 0) continue;

        echo "  channel:             {$ch['channel']}\n";
        echo "  application:         {$ch['application']}\n";
        echo "  application_data:    {$ch['application_data']}\n";
        echo "  state_desc:          {$ch['state_desc']}\n";
        echo "  caller_id_num:       {$ch['caller_id_num']}\n";
        echo "  caller_id_name:      {$ch['caller_id_name']}\n";
        echo "  connected_line_num:  {$ch['connected_line_num']}\n";
        echo "  connected_line_name: {$ch['connected_line_name']}\n";
        echo "  duration:            {$ch['duration']}\n";
        echo "  ---\n";
    }
    if (count($channels) === 0) {
        echo "  (no active calls - cannot test ConnectedLine fields)\n";
        echo "  Run this script DURING an active call to see the data\n";
    }
} catch (\Exception $e) {
    echo "AMI ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Check what JS would render
echo "=== 6. Full API output (agents array as JSON) ===\n";
$apiAgents = [];
foreach ($agents as $agent) {
    $apiAgents[] = $agent;
}
// Only show the first 10 for brevity
echo json_encode(array_slice($apiAgents, 0, 15), JSON_PRETTY_PRINT) . "\n";

echo "\n=== Debug complete ===\n";
