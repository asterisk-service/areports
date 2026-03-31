<?php
/**
 * Queue Report Controller
 * Handles queue performance reports
 */

namespace aReports\Controllers;

use aReports\Core\App;
use aReports\Core\Controller;
use aReports\Services\QueueService;

class QueueReportController extends Controller
{
    private QueueService $queueService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->queueService = new QueueService();
    }

    /**
     * Get queue list filtered to user's assigned queues
     */
    private function getFilteredQueueList(): array
    {
        $queueList = $this->queueService->getQueueList();
        $allowedQueues = $this->getUserQueues();

        if ($allowedQueues !== null) {
            $queueList = array_filter($queueList, function ($q) use ($allowedQueues) {
                return in_array($q['name'], $allowedQueues);
            });
            $queueList = array_values($queueList);
        }

        return $queueList;
    }

    /**
     * Restrict queue filter parameter to user's assigned queues
     */
    private function restrictQueueFilter(mixed $queueFilter): mixed
    {
        $allowedQueues = $this->getUserQueues();
        if ($allowedQueues === null) {
            return $queueFilter; // Admin sees all
        }

        if (empty($allowedQueues)) {
            return ['__none__']; // No queues assigned, force empty results
        }

        if ($queueFilter === null) {
            return $allowedQueues; // Default to all allowed queues
        }

        // Intersect user's filter with allowed queues
        if (is_array($queueFilter)) {
            $filtered = array_values(array_intersect($queueFilter, $allowedQueues));
            return empty($filtered) ? ['__none__'] : $filtered;
        }

        // Single queue filter
        return in_array($queueFilter, $allowedQueues) ? $queueFilter : '__none__';
    }

    /**
     * Queue summary report (default)
     */
    public function index(): void
    {
        $this->summary();
    }

    /**
     * Queue summary report
     */
    public function summary(): void
    {
        $this->requirePermission('reports.queue.view');

        $dateFrom = $this->get('date_from', date('Y-m-d'));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        // Support multi-queue filter (array from select multiple)
        $queueFilter = $_GET['queue'] ?? null;
        if (is_array($queueFilter)) {
            $queueFilter = array_filter($queueFilter);
            if (empty($queueFilter)) {
                $queueFilter = null;
            }
        }
        $queueFilter = $this->restrictQueueFilter($queueFilter);

        $queues = $this->queueService->getQueueSummary($dateFrom, $dateTo, $queueFilter);
        $queueList = $this->getFilteredQueueList();
        $firstQueue = is_array($queueFilter) ? ($queueFilter[0] ?? null) : $queueFilter;
        $hourly = $this->queueService->getQueueHourly(date('Y-m-d'), $firstQueue);

        // Calculate totals
        $totals = [
            'total_calls' => 0,
            'answered' => 0,
            'abandoned' => 0,
            'avg_wait' => 0,
            'agents_count' => 0
        ];

        $allAgents = [];
        foreach ($queues as $queue) {
            $totals['total_calls'] += $queue['total_calls'];
            $totals['answered'] += $queue['answered'];
            $totals['abandoned'] += $queue['abandoned'];
            $totals['agents_count'] += $queue['agents_count'];
        }

        $totals['answer_rate'] = $totals['total_calls'] > 0
            ? round(($totals['answered'] / $totals['total_calls']) * 100, 1)
            : 0;
        $totals['abandon_rate'] = $totals['total_calls'] > 0
            ? round(($totals['abandoned'] / $totals['total_calls']) * 100, 1)
            : 0;

        $this->render('reports/queue/summary', [
            'title' => 'Сводка по очередям',
            'currentPage' => 'reports.queue.summary',
            'queues' => $queues,
            'queueList' => $queueList,
            'hourly' => $hourly,
            'totals' => $totals,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'queueFilter' => $queueFilter
        ]);
    }

    /**
     * SLA report
     */
    public function sla(): void
    {
        $this->requirePermission('reports.queue.view');

        $dateFrom = $this->get('date_from', date('Y-m-d'));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        $queueFilter = $this->get('queue');

        $queueFilter = $this->restrictQueueFilter($queueFilter);
        $slaData = $this->queueService->getQueueSLA($dateFrom, $dateTo);
        $queueList = $this->getFilteredQueueList();
        $trend = $this->queueService->getDailyTrend($dateFrom, $dateTo, $queueFilter);

        $this->render('reports/queue/sla', [
            'title' => 'Queue SLA Report',
            'currentPage' => 'reports.queue.sla',
            'slaData' => $slaData,
            'queueList' => $queueList,
            'trend' => $trend,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'queueFilter' => $queueFilter
        ]);
    }

    /**
     * Abandonment report
     */
    public function abandonment(): void
    {
        $this->requirePermission('reports.queue.view');

        $dateFrom = $this->get('date_from', date('Y-m-d'));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        $queueFilter = $this->get('queue');
        $page = (int) $this->get('page', 1);
        $perPage = 50;

        $queueFilter = $this->restrictQueueFilter($queueFilter);
        $result = $this->queueService->getAbandonedCalls($dateFrom, $dateTo, $queueFilter, $perPage, ($page - 1) * $perPage);
        $queueList = $this->getFilteredQueueList();

        $totalPages = ceil($result['total'] / $perPage);

        $this->render('reports/queue/abandonment', [
            'title' => 'Abandonment Report',
            'currentPage' => 'reports.queue.abandonment',
            'records' => $result['records'],
            'total' => $result['total'],
            'queueList' => $queueList,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'queueFilter' => $queueFilter,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage
        ]);
    }

    /**
     * Wait times distribution
     */
    public function waitTimes(): void
    {
        $this->requirePermission('reports.queue.view');

        $dateFrom = $this->get('date_from', date('Y-m-d', strtotime('-7 days')));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        $queueFilter = $this->get('queue');

        $queueFilter = $this->restrictQueueFilter($queueFilter);
        $distribution = $this->queueService->getWaitTimeDistribution($dateFrom, $dateTo, $queueFilter);
        $queueList = $this->getFilteredQueueList();
        $queues = $this->queueService->getQueueSummary($dateFrom, $dateTo, $queueFilter);

        $this->render('reports/queue/wait-times', [
            'title' => 'Wait Times Analysis',
            'currentPage' => 'reports.queue.waittimes',
            'distribution' => $distribution,
            'queues' => $queues,
            'queueList' => $queueList,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'queueFilter' => $queueFilter
        ]);
    }

    /**
     * Export queue report
     */
    public function export(): void
    {
        $this->requirePermission('reports.queue.export');

        $dateFrom = $this->get('date_from', date('Y-m-d', strtotime('-7 days')));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        $queueFilter = $this->get('queue');

        $queueFilter = $this->restrictQueueFilter($queueFilter);
        $csv = $this->queueService->exportToCSV($dateFrom, $dateTo, $queueFilter);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="queue_report_' . $dateFrom . '_' . $dateTo . '.csv"');
        echo $csv;
        exit;
    }
}
