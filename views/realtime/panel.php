<?php $this->section('styles'); ?>
<style>
.realtime-control-panel {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px 15px;
    margin-bottom: 20px;
}
.realtime-control-panel .control-group {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}
.realtime-control-panel .control-item {
    display: flex;
    align-items: center;
    gap: 5px;
}
.realtime-control-panel .control-item label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    margin: 0;
    text-transform: uppercase;
}
.realtime-control-panel .control-item select,
.realtime-control-panel .control-item input {
    font-size: 13px;
    padding: 4px 8px;
    height: auto;
}
.update-time {
    background: #e9ecef;
    padding: 5px 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 14px;
}
.update-time.stale {
    background: #f8d7da;
    color: #721c24;
}
.queue-list-display {
    font-size: 12px;
    color: #6c757d;
    padding: 5px 0;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 10px;
}
.realtime-table th {
    background: #f1f3f5;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #495057;
    white-space: nowrap;
    padding: 8px 10px !important;
}
.realtime-table td {
    padding: 6px 10px !important;
    vertical-align: middle;
    font-size: 13px;
}
.realtime-table .highlight-zero {
    background-color: #fff3cd !important;
}
.realtime-table .highlight-danger {
    background-color: #f8d7da !important;
}
.realtime-table .highlight-success {
    background-color: #d4edda !important;
}
.queue-icon {
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #007bff;
    color: white;
    border-radius: 3px;
    font-size: 10px;
    margin-right: 5px;
}
.section-header {
    font-weight: 600;
    color: #495057;
    padding: 10px 0;
    margin-top: 20px;
    border-bottom: 2px solid #dee2e6;
}
.export-buttons {
    display: flex;
    gap: 5px;
    align-items: center;
    margin: 10px 0;
}
.export-buttons .btn {
    padding: 2px 8px;
    font-size: 12px;
}
.export-buttons span {
    color: #6c757d;
    font-size: 12px;
}
.agent-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}
.agent-status-badge.ready {
    background: #d4edda;
    color: #155724;
}
.agent-status-badge.paused {
    background: #fff3cd;
    color: #856404;
}
.agent-status-badge.busy {
    background: #cce5ff;
    color: #004085;
}
.agent-status-badge.unavailable {
    background: #f8d7da;
    color: #721c24;
}
.call-duration {
    font-family: monospace;
}
.no-data-row td {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 20px !important;
}
.tab-link {
    padding: 8px 16px;
    text-decoration: none;
    color: #495057;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    margin-right: 5px;
}
.tab-link:hover {
    color: #007bff;
}
.tab-link.active {
    color: #dc3545;
    border-bottom-color: #dc3545;
}
.summary-row {
    background: #e9ecef !important;
    font-weight: 600;
}
</style>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<!-- Page Header with Tabs -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center">
        <a href="/areports/dashboard" class="tab-link">Главная</a>
        <a href="/areports/realtime" class="tab-link active">Реальное время</a>
        <a href="/areports/wallboard" class="tab-link">Табло</a>
    </div>
</div>

<!-- Control Panel -->
<div class="realtime-control-panel">
    <div class="control-group">
        <button type="button" class="btn btn-secondary btn-sm" id="btnReload">
            <i class="fas fa-sync-alt me-1"></i> Обновить
        </button>

        <div class="control-item">
            <label>Обновлено</label>
            <span class="update-time" id="updateTime">--:--:--</span>
            <span id="connectionStatus" class="ms-1" title="Статус соединения">
                <i class="fas fa-circle text-secondary" style="font-size: 8px;"></i>
            </span>
        </div>

        <div class="control-item">
            <label>Интервал</label>
            <select id="refreshInterval" class="form-select form-select-sm">
                <option value="5">5с</option>
                <option value="10">10с</option>
                <option value="30">30с</option>
                <option value="60">60с</option>
                <option value="0">Вручную</option>
            </select>
        </div>

        <div class="control-item">
            <label>Сводка</label>
            <select id="showRecap" class="form-select form-select-sm">
                <option value="1">Показать</option>
                <option value="0">Скрыть</option>
            </select>
        </div>

        <div class="control-item">
            <label>Звонки</label>
            <select id="showCalls" class="form-select form-select-sm">
                <option value="1">Показать</option>
                <option value="0">Скрыть</option>
            </select>
        </div>

        <div class="control-item">
            <label>Операторы</label>
            <select id="showAgents" class="form-select form-select-sm">
                <option value="1">Показать</option>
                <option value="0">Скрыть</option>
            </select>
        </div>

        <div class="control-item">
            <label>Очереди</label>
            <select id="filterQueues" class="form-select form-select-sm" style="min-width: 100px;">
                <option value="">Все</option>
                <?php foreach ($queues as $queue): ?>
                <option value="<?= $this->e($queue['queue_number']) ?>"><?= $this->e($queue['display_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="control-item">
            <label>Операторы</label>
            <select id="filterAgents" class="form-select form-select-sm">
                <option value="all">Все</option>
                <option value="members" selected>Участники</option>
                <option value="logged">В системе</option>
                <option value="paused">На паузе</option>
                <option value="available">Свободные</option>
            </select>
        </div>

        <div class="control-item ms-auto">
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddMember" title="Добавить оператора в очередь">
                <i class="fas fa-user-plus me-1"></i> Добавить
            </button>
        </div>
    </div>
</div>

<!-- Queue List Display -->
<div class="queue-list-display" id="queueListDisplay">
    <strong>Очереди:</strong> <span id="selectedQueues">Загрузка...</span>
</div>

<!-- Queue Summary Section -->
<div id="sectionRecap">
    <div class="table-responsive">
        <table class="table table-bordered table-hover realtime-table mb-0" id="queueSummaryTable">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>Очередь</th>
                    <th class="text-center">Операторов</th>
                    <th class="text-center">Свободных</th>
                    <th class="text-center">На паузе</th>
                    <th class="text-center">Недост.</th>
                    <th class="text-center">Занят</th>
                    <th class="text-center">Ожидают</th>
                    <th class="text-center">Входящие</th>
                    <th class="text-center">Исходящие</th>
                </tr>
            </thead>
            <tbody id="queueSummaryBody">
                <tr class="no-data-row">
                    <td colspan="10">Загрузка данных очередей...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="export-buttons">
        <span><i class="fas fa-caret-right"></i> Экспорт...</span>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTable('queue', 'excel')">
            <i class="fas fa-file-excel"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTable('queue', 'csv')">
            <i class="fas fa-file-csv"></i>
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="exportTable('queue', 'clipboard')">
            <i class="fas fa-clipboard"></i>
        </button>
    </div>
</div>

<!-- Calls Being Processed Section -->
<div id="sectionCalls">
    <div class="section-header">Обрабатываемые звонки:</div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover realtime-table mb-0" id="callsTable">
            <thead>
                <tr>
                    <th>Очередь</th>
                    <th>Звонящий</th>
                    <th>Поступил</th>
                    <th>IVR</th>
                    <th>Ожидание</th>
                    <th>Длительность</th>
                    <th>Оператор</th>
                    <th>MOH</th>
                    <th>Обсл.</th>
                </tr>
            </thead>
            <tbody id="callsBody">
                <tr class="no-data-row">
                    <td colspan="9">Нет активных звонков</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="export-buttons">
        <span><i class="fas fa-caret-right"></i> Экспорт...</span>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTable('calls', 'excel')">
            <i class="fas fa-file-excel"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTable('calls', 'csv')">
            <i class="fas fa-file-csv"></i>
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="exportTable('calls', 'clipboard')">
            <i class="fas fa-clipboard"></i>
        </button>
    </div>
</div>

<!-- Agents Currently Logged In Section -->
<div id="sectionAgents">
    <div class="section-header">Операторы в системе:</div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover realtime-table mb-0" id="agentsTable">
            <thead>
                <tr>
                    <th>Оператор</th>
                    <th>Вход в систему</th>
                    <th>Очереди</th>
                    <th>Внутр. номер</th>
                    <th class="text-center">Пауза</th>
                    <th class="text-center">Обсл.</th>
                    <th>Посл. звонок</th>
                    <th>В очереди</th>
                </tr>
            </thead>
            <tbody id="agentsBody">
                <tr class="no-data-row">
                    <td colspan="8">Нет операторов в системе</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="export-buttons">
        <span><i class="fas fa-caret-right"></i> Экспорт...</span>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTable('agents', 'excel')">
            <i class="fas fa-file-excel"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTable('agents', 'csv')">
            <i class="fas fa-file-csv"></i>
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" onclick="exportTable('agents', 'clipboard')">
            <i class="fas fa-clipboard"></i>
        </button>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить оператора в очередь</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Очередь</label>
                    <select id="addMemberQueue" class="form-select">
                        <?php foreach ($queues as $queue): ?>
                        <option value="<?= $this->e($queue['queue_number']) ?>"><?= $this->e($queue['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Интерфейс оператора</label>
                    <input type="text" id="addMemberInterface" class="form-control" placeholder="напр., PJSIP/100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="btnConfirmAddMember">Добавить</button>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
var RealtimePanel = {
    refreshTimer: null,
    lastUpdate: null,
    isLoading: false,

    init: function() {
        this.bindEvents();
        this.loadData();
        this.startAutoRefresh();
        this.loadPreferences();
    },

    bindEvents: function() {
        var self = this;

        $('#btnReload').on('click', function() {
            self.loadData();
        });

        $('#refreshInterval').on('change', function() {
            self.startAutoRefresh();
            self.savePreference('refreshInterval', $(this).val());
        });

        $('#showRecap').on('change', function() {
            $('#sectionRecap').toggle($(this).val() === '1');
            self.savePreference('showRecap', $(this).val());
        });

        $('#showCalls').on('change', function() {
            $('#sectionCalls').toggle($(this).val() === '1');
            self.savePreference('showCalls', $(this).val());
        });

        $('#showAgents').on('change', function() {
            $('#sectionAgents').toggle($(this).val() === '1');
            self.savePreference('showAgents', $(this).val());
        });

        $('#filterQueues, #filterAgents').on('change', function() {
            self.loadData();
        });

        $('#btnAddMember').on('click', function() {
            $('#addMemberModal').modal('show');
        });

        $('#btnConfirmAddMember').on('click', function() {
            self.addMember();
        });
    },

    loadPreferences: function() {
        var prefs = localStorage.getItem('realtimePanelPrefs');
        if (prefs) {
            prefs = JSON.parse(prefs);
            if (prefs.refreshInterval) $('#refreshInterval').val(prefs.refreshInterval);
            if (prefs.showRecap !== undefined) {
                $('#showRecap').val(prefs.showRecap);
                $('#sectionRecap').toggle(prefs.showRecap === '1');
            }
            if (prefs.showCalls !== undefined) {
                $('#showCalls').val(prefs.showCalls);
                $('#sectionCalls').toggle(prefs.showCalls === '1');
            }
            if (prefs.showAgents !== undefined) {
                $('#showAgents').val(prefs.showAgents);
                $('#sectionAgents').toggle(prefs.showAgents === '1');
            }
        }
    },

    savePreference: function(key, value) {
        var prefs = localStorage.getItem('realtimePanelPrefs');
        prefs = prefs ? JSON.parse(prefs) : {};
        prefs[key] = value;
        localStorage.setItem('realtimePanelPrefs', JSON.stringify(prefs));
    },

    startAutoRefresh: function() {
        var self = this;
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }

        var interval = parseInt($('#refreshInterval').val());
        if (interval > 0) {
            this.refreshTimer = setInterval(function() {
                self.loadData();
            }, interval * 1000);
        }
    },

    loadData: function() {
        if (this.isLoading) return;

        var self = this;
        this.isLoading = true;

        $('#btnReload').prop('disabled', true).find('i').addClass('fa-spin');

        var queueFilter = $('#filterQueues').val();
        var agentFilter = $('#filterAgents').val();

        // Load all data in parallel
        $.when(
            $.get('/areports/api/realtime/queues'),
            $.get('/areports/api/realtime/agents'),
            $.get('/areports/api/realtime/calls')
        ).done(function(queueRes, agentRes, callsRes) {
            var queueData = queueRes[0];
            var agentData = agentRes[0];
            var callsData = callsRes[0];

            if (queueData.success) {
                self.renderQueues(queueData.data, queueFilter);
            }

            if (agentData.success) {
                self.renderAgents(agentData.data, agentFilter);
            }

            if (callsData.success) {
                self.renderCalls(callsData.data, queueFilter);
            }

            self.updateTimestamp();
            self.setConnectionStatus(true);

        }).fail(function() {
            self.setConnectionStatus(false);
            aReports.toast('Ошибка загрузки данных', 'error');
        }).always(function() {
            self.isLoading = false;
            $('#btnReload').prop('disabled', false).find('i').removeClass('fa-spin');
        });
    },

    renderQueues: function(queues, filter) {
        var $tbody = $('#queueSummaryBody');
        $tbody.empty();

        if (!queues || queues.length === 0) {
            $tbody.html('<tr class="no-data-row"><td colspan="10">Нет данных по очередям</td></tr>');
            $('#selectedQueues').text('Нет');
            return;
        }

        // Filter queues if needed
        if (filter) {
            queues = queues.filter(function(q) {
                return q.name === filter;
            });
        }

        // Update queue list display
        var queueNames = queues.map(function(q) { return q.name; });
        $('#selectedQueues').text(queueNames.join(' | '));

        // Calculate totals
        var totals = {
            agents: 0,
            ready: 0,
            paused: 0,
            unknown: 0,
            busy: 0,
            waiting: 0,
            inbound: 0,
            outbound: 0
        };

        // Render each queue
        queues.forEach(function(queue) {
            var stats = this.calculateQueueStats(queue);

            totals.agents += stats.totalAgents;
            totals.ready += stats.readyAgents;
            totals.paused += stats.pausedAgents;
            totals.unknown += stats.unknownAgents;
            totals.busy += stats.busyAgents;
            totals.waiting += queue.calls || 0;
            totals.inbound += stats.inboundCalls;
            totals.outbound += stats.outboundCalls;

            var readyClass = stats.readyAgents === 0 ? 'highlight-zero' : '';

            var $row = $('<tr>');
            $row.html(
                '<td><span class="queue-icon"><i class="fas fa-layer-group"></i></span></td>' +
                '<td>' + this.escapeHtml(queue.name) + '</td>' +
                '<td class="text-center">' + stats.totalAgents + '</td>' +
                '<td class="text-center ' + readyClass + '">' + stats.readyAgents + '</td>' +
                '<td class="text-center">' + stats.pausedAgents + '</td>' +
                '<td class="text-center">' + stats.unknownAgents + '</td>' +
                '<td class="text-center">' + stats.busyAgents + '</td>' +
                '<td class="text-center">' + (queue.calls || 0) + '</td>' +
                '<td class="text-center">' + stats.inboundCalls + '</td>' +
                '<td class="text-center">' + stats.outboundCalls + '</td>'
            );
            $tbody.append($row);
        }, this);

        // Add summary row if multiple queues
        if (queues.length > 1) {
            var readyClass = totals.ready === 0 ? 'highlight-zero' : '';
            var $summaryRow = $('<tr class="summary-row">');
            $summaryRow.html(
                '<td></td>' +
                '<td>Все выбранные</td>' +
                '<td class="text-center">' + totals.agents + '</td>' +
                '<td class="text-center ' + readyClass + '">' + totals.ready + '</td>' +
                '<td class="text-center">' + totals.paused + '</td>' +
                '<td class="text-center">' + totals.unknown + '</td>' +
                '<td class="text-center">' + totals.busy + '</td>' +
                '<td class="text-center">' + totals.waiting + '</td>' +
                '<td class="text-center">' + totals.inbound + '</td>' +
                '<td class="text-center">' + totals.outbound + '</td>'
            );
            $tbody.prepend($summaryRow);
        }
    },

    calculateQueueStats: function(queue) {
        var stats = {
            totalAgents: 0,
            readyAgents: 0,
            pausedAgents: 0,
            unknownAgents: 0,
            busyAgents: 0,
            inboundCalls: 0,
            outboundCalls: 0
        };

        if (queue.members && queue.members.length > 0) {
            queue.members.forEach(function(member) {
                stats.totalAgents++;

                if (member.paused) {
                    stats.pausedAgents++;
                } else if (member.status === 0) {
                    stats.unknownAgents++;
                } else if (member.status === 1) {
                    stats.readyAgents++;
                } else if (member.status === 2 || member.status === 6 || member.status === 7) {
                    stats.busyAgents++;
                    if (member.in_call) {
                        stats.inboundCalls++;
                    }
                } else if (member.status === 5) {
                    stats.unknownAgents++;
                } else {
                    stats.readyAgents++;
                }
            });
        }

        return stats;
    },

    renderCalls: function(calls, queueFilter) {
        var $tbody = $('#callsBody');
        $tbody.empty();

        var activeCalls = calls || [];

        if (activeCalls.length === 0) {
            $tbody.html('<tr class="no-data-row"><td colspan="9">Нет активных звонков</td></tr>');
            return;
        }

        activeCalls.forEach(function(call) {
            var $row = $('<tr>');
            $row.html(
                '<td>' + this.escapeHtml(call.context || '-') + '</td>' +
                '<td>' + this.escapeHtml(call.caller_id || call.caller_id_num || '-') + '</td>' +
                '<td>' + this.formatTime(call.entered || 0) + '</td>' +
                '<td>' + this.formatDuration(call.ivr_time || 0) + '</td>' +
                '<td>' + this.formatDuration(call.wait || call.waiting || 0) + '</td>' +
                '<td class="call-duration">' + this.formatDuration(call.duration || 0) + '</td>' +
                '<td>' + this.escapeHtml(call.connected_to || call.agent || '-') + '</td>' +
                '<td>' + (call.moh ? 'Да' : '-') + '</td>' +
                '<td>' + (call.srv || '-') + '</td>'
            );
            $tbody.append($row);
        }, this);
    },

    renderAgents: function(agents, filter) {
        var $tbody = $('#agentsBody');
        $tbody.empty();

        if (!agents || agents.length === 0) {
            $tbody.html('<tr class="no-data-row"><td colspan="8">Нет операторов в системе</td></tr>');
            return;
        }

        // Apply filter
        var filteredAgents = agents;
        if (filter === 'logged') {
            filteredAgents = agents.filter(function(a) { return a.status !== 5; });
        } else if (filter === 'paused') {
            filteredAgents = agents.filter(function(a) { return a.paused; });
        } else if (filter === 'available') {
            filteredAgents = agents.filter(function(a) { return !a.paused && a.status === 1; });
        }

        if (filteredAgents.length === 0) {
            $tbody.html('<tr class="no-data-row"><td colspan="8">Нет операторов по фильтру</td></tr>');
            return;
        }

        filteredAgents.forEach(function(agent) {
            var pauseStatus = '-';
            var pauseClass = '';

            if (agent.paused) {
                pauseStatus = agent.paused_reason || 'На паузе';
                pauseClass = 'highlight-zero';
            }

            var queueList = (agent.queues || []).join(', ') || '-';
            var extension = agent.interface ? agent.interface.replace('PJSIP/', '').replace('SIP/', '') : '-';

            var $row = $('<tr>');
            $row.html(
                '<td>' + this.escapeHtml(agent.name || agent.interface) + '</td>' +
                '<td>' + this.formatLastLogin(agent.last_login) + '</td>' +
                '<td>' + this.escapeHtml(queueList) + '</td>' +
                '<td>' + this.escapeHtml(extension) + '</td>' +
                '<td class="text-center ' + pauseClass + '">' + this.escapeHtml(pauseStatus) + '</td>' +
                '<td class="text-center">' + (agent.calls_taken || 0) + '</td>' +
                '<td>' + this.formatLastCall(agent.last_call) + '</td>' +
                '<td>' + this.formatDuration(agent.queue_time || 0) + '</td>'
            );
            $tbody.append($row);
        }, this);
    },

    updateTimestamp: function() {
        var now = new Date();
        var timeStr = now.toTimeString().split(' ')[0];
        $('#updateTime').text(timeStr).removeClass('stale');
        this.lastUpdate = now;
    },

    setConnectionStatus: function(connected) {
        var $icon = $('#connectionStatus i');
        if (connected) {
            $icon.removeClass('text-danger text-secondary').addClass('text-success');
        } else {
            $icon.removeClass('text-success text-secondary').addClass('text-danger');
        }
    },

    addMember: function() {
        var queue = $('#addMemberQueue').val();
        var iface = $('#addMemberInterface').val();

        if (!queue || !iface) {
            aReports.toast('Заполните все поля', 'warning');
            return;
        }

        $.ajax({
            url: '/areports/agent/queue-login',
            method: 'POST',
            data: {
                queue: queue,
                interface: iface,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.success) {
                    aReports.toast('Оператор добавлен', 'success');
                    $('#addMemberModal').modal('hide');
                    $('#addMemberInterface').val('');
                    RealtimePanel.loadData();
                } else {
                    aReports.toast(res.message || 'Ошибка добавления', 'error');
                }
            },
            error: function() {
                aReports.toast('Ошибка добавления оператора', 'error');
            }
        });
    },

    // Utility functions
    escapeHtml: function(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    },

    formatDuration: function(seconds) {
        if (!seconds || seconds <= 0) return '0:00';
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    },

    formatTime: function(timestamp) {
        if (!timestamp) return '-';
        var date = new Date(timestamp * 1000);
        return date.toLocaleTimeString();
    },

    formatLastLogin: function(timestamp) {
        if (!timestamp) return '-';
        var date = new Date(timestamp * 1000);
        return date.toLocaleString();
    },

    formatLastCall: function(timestamp) {
        if (!timestamp || timestamp === 0) return 'Нет';
        var now = Math.floor(Date.now() / 1000);
        var diff = now - timestamp;

        if (diff < 60) return diff + 'с назад';
        if (diff < 3600) return Math.floor(diff / 60) + 'м назад';
        if (diff < 86400) return Math.floor(diff / 3600) + 'ч назад';
        return Math.floor(diff / 86400) + 'д назад';
    }
};

function exportTable(type, format) {
    var tableId = type === 'queue' ? 'queueSummaryTable' :
                  type === 'calls' ? 'callsTable' : 'agentsTable';

    var $table = $('#' + tableId);
    var data = [];

    // Get headers
    var headers = [];
    $table.find('thead th').each(function() {
        headers.push($(this).text().trim());
    });
    data.push(headers);

    // Get rows
    $table.find('tbody tr:not(.no-data-row)').each(function() {
        var row = [];
        $(this).find('td').each(function() {
            row.push($(this).text().trim());
        });
        if (row.length > 0) {
            data.push(row);
        }
    });

    if (format === 'clipboard') {
        var text = data.map(function(row) { return row.join('\t'); }).join('\n');
        navigator.clipboard.writeText(text).then(function() {
            aReports.toast('Скопировано в буфер', 'success');
        });
    } else if (format === 'csv') {
        var csv = data.map(function(row) {
            return row.map(function(cell) {
                return '"' + String(cell).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\n');

        var blob = new Blob([csv], { type: 'text/csv' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'realtime_' + type + '_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    } else {
        window.location.href = '/areports/export/' + type + '?format=xlsx';
    }
}

// Initialize on document ready
$(document).ready(function() {
    RealtimePanel.init();
});
</script>
<?php $this->endSection(); ?>
