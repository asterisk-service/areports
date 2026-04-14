#!/usr/bin/env php
<?php
/**
 * Debug: test supervisor report filtering
 * Run: php tools/debug_reports.php [user_id]
 */
define('BASE_PATH', dirname(__DIR__));
chdir(BASE_PATH);

// Autoloader from index.php
spl_autoload_register(function ($class) {
    $prefix = 'aReports\\';
    $baseDir = BASE_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', lcfirst($relativeClass)) . '.php';
    $mappings = [
        'Core\\' => 'core/', 'Controllers\\' => 'controllers/',
        'Models\\' => 'models/', 'Services\\' => 'services/', 'Middleware\\' => 'middleware/',
    ];
    foreach ($mappings as $nsPrefix => $dir) {
        if (strncmp($nsPrefix, $relativeClass, strlen($nsPrefix)) === 0) {
            $classFile = substr($relativeClass, strlen($nsPrefix));
            $file = $baseDir . $dir . str_replace('\\', '/', $classFile) . '.php';
            break;
        }
    }
    if (file_exists($file)) require $file;
});

use aReports\Core\App;
use aReports\Services\QueueService;
use aReports\Services\AgentService;

$app = App::getInstance();
$db = $app->getDb();
$cdrDb = $app->getCdrDb();

$userId = $argv[1] ?? 17; // Default: elena
echo "=== Report Debug for user_id={$userId} ===\n\n";

// 1. User info
$user = $db->fetch("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?", [$userId]);
echo "1. User: {$user['username']}, role: {$user['role_name']}, ext: {$user['extension']}\n";
echo "   isAdmin: " . ($user['role_name'] === 'admin' ? 'YES' : 'NO') . "\n\n";

// 2. User queues
$queues = $db->fetchAll("SELECT queue_name FROM user_queues WHERE user_id = ?", [$userId]);
$queueNames = array_column($queues, 'queue_name');
echo "2. user_queues: " . (empty($queueNames) ? '(none)' : implode(', ', $queueNames)) . "\n";
echo "   Count: " . count($queueNames) . "\n\n";

// 3. getUserQueues() logic
if ($user['role_name'] === 'admin') {
    $allowedQueues = null;
    echo "3. getUserQueues() = null (admin, no filter)\n\n";
} elseif (empty($queueNames)) {
    $allowedQueues = null;
    echo "3. getUserQueues() = null (no queues assigned, no filter)\n\n";
} else {
    $allowedQueues = $queueNames;
    echo "3. getUserQueues() = [" . implode(', ', $allowedQueues) . "]\n\n";
}

// 4. Queue list from queuelog
$queueService = new QueueService();
$allQueues = $queueService->getQueueList();
echo "4. All queues from queuelog: " . count($allQueues) . "\n";
foreach ($allQueues as $q) {
    echo "   - {$q['name']} ({$q['display_name']})\n";
}
echo "\n";

// 5. Filtered queue list
if ($allowedQueues !== null) {
    $filtered = array_filter($allQueues, function ($q) use ($allowedQueues) {
        return in_array($q['name'], $allowedQueues);
    });
    echo "5. Filtered queues (for supervisor): " . count($filtered) . "\n";
    foreach ($filtered as $q) {
        echo "   - {$q['name']} ({$q['display_name']})\n";
    }
} else {
    echo "5. Filtered queues: ALL (no filter)\n";
}
echo "\n";

// 6. Allowed agents
if ($allowedQueues !== null && !empty($allowedQueues)) {
    $ph = implode(',', array_fill(0, count($allowedQueues), '?'));
    $agentRows = $cdrDb->fetchAll(
        "SELECT DISTINCT agent FROM queuelog WHERE queuename IN ($ph) AND agent != 'NONE' AND agent != '' AND agent IS NOT NULL",
        $allowedQueues
    );
    $allowedAgents = array_column($agentRows, 'agent');
    echo "6. Allowed agents from queuelog: " . count($allowedAgents) . "\n";
    foreach (array_slice($allowedAgents, 0, 20) as $a) {
        echo "   - {$a}\n";
    }
    if (count($allowedAgents) > 20) echo "   ... and " . (count($allowedAgents) - 20) . " more\n";
} else {
    echo "6. Allowed agents: ALL (no filter)\n";
}
echo "\n";

// 7. Agent list
$agentService = new AgentService();
$allAgents = $agentService->getAgentList();
echo "7. All agents from queuelog: " . count($allAgents) . "\n";
foreach (array_slice($allAgents, 0, 10) as $a) {
    echo "   - {$a['agent']} (ext: {$a['extension']}, name: {$a['display_name']})\n";
}
if (count($allAgents) > 10) echo "   ... and " . (count($allAgents) - 10) . " more\n";
echo "\n";

// 8. Queue summary data (today)
$today = date('Y-m-d');
$queueFilter = $allowedQueues; // restrictQueueFilter(null) returns $allowedQueues
echo "8. Queue summary ({$today}), filter: " . ($queueFilter === null ? 'ALL' : json_encode($queueFilter)) . "\n";
$summary = $queueService->getQueueSummary($today, $today, $queueFilter);
echo "   Results: " . count($summary) . " queues\n";
foreach (array_slice($summary, 0, 10) as $s) {
    echo "   - {$s['queuename']}: {$s['total_calls']} calls, {$s['answered']} answered\n";
}
echo "\n";

// 9. Check permissions
$perms = $db->fetchAll(
    "SELECT p.name FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? ORDER BY p.name",
    [$user['role_id']]
);
$permNames = array_column($perms, 'name');
echo "9. Role permissions (" . count($permNames) . " total):\n";
$reportPerms = array_filter($permNames, fn($p) => str_starts_with($p, 'reports.') || str_starts_with($p, 'quality.'));
foreach ($reportPerms as $p) {
    echo "   - {$p}\n";
}
echo "\n";

echo "=== Debug complete ===\n";
