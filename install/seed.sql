-- ============================================
-- aReports Seed Data
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------
-- ROLES
-- --------------------------------------------

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`) VALUES
(1, 'admin', 'Administrator', 'Full system access - can manage all settings, users, and view all reports', 1),
(2, 'supervisor', 'Supervisor', 'Queue and agent management, access to all reports and quality monitoring', 1),
(3, 'agent', 'Agent', 'View own statistics and limited queue reports', 1);

-- --------------------------------------------
-- PERMISSIONS
-- --------------------------------------------

INSERT INTO `permissions` (`id`, `name`, `display_name`, `category`, `description`) VALUES
-- Dashboard
(1, 'dashboard.view', 'View Dashboard', 'Dashboard', NULL),
(2, 'dashboard.customize', 'Customize Dashboard', 'Dashboard', NULL),
(3, 'wallboard.view', 'View Wallboard', 'Dashboard', NULL),
(4, 'wallboard.customize', 'Customize Wallboard', 'Dashboard', NULL),
-- Monitoring
(5, 'realtime.view', 'View Real-time Data', 'Monitoring', NULL),
(6, 'realtime.queue_status', 'View Queue Status', 'Monitoring', NULL),
(7, 'realtime.agent_status', 'View Agent Status', 'Monitoring', NULL),
(8, 'realtime.active_calls', 'View Active Calls', 'Monitoring', NULL),
-- Reports
(9, 'reports.agent.view', 'View Agent Reports', 'Reports', NULL),
(10, 'reports.agent.view_all', 'View All Agents', 'Reports', NULL),
(11, 'reports.agent.view_own', 'View Own Stats Only', 'Reports', NULL),
(12, 'reports.queue.view', 'View Queue Reports', 'Reports', NULL),
(13, 'reports.queue.sla', 'View SLA Reports', 'Reports', NULL),
(14, 'reports.cdr.view', 'View Call Detail Reports', 'Reports', NULL),
(15, 'reports.cdr.export', 'Export CDR Data', 'Reports', NULL),
(16, 'reports.cdr.listen', 'Listen to Recordings', 'Reports', NULL),
-- Quality
(17, 'quality.view', 'View Quality Reports', 'Quality', NULL),
(18, 'quality.evaluate', 'Evaluate Calls', 'Quality', NULL),
(19, 'quality.manage_forms', 'Manage Evaluation Forms', 'Quality', NULL),
-- Report Builder
(20, 'reports.builder', 'Use Report Builder', 'Reports', NULL),
(21, 'reports.schedule', 'Schedule Reports', 'Reports', NULL),
(22, 'reports.export', 'Export Reports', 'Reports', NULL),
-- Alerts
(23, 'alerts.view', 'View Alerts', 'Alerts', NULL),
(24, 'alerts.manage', 'Manage Alerts', 'Alerts', NULL),
(25, 'alerts.acknowledge', 'Acknowledge Alerts', 'Alerts', NULL),
-- Admin
(26, 'admin.users.view', 'View Users', 'Administration', NULL),
(27, 'admin.users.manage', 'Manage Users', 'Administration', NULL),
(28, 'admin.roles.view', 'View Roles', 'Administration', NULL),
(29, 'admin.roles.manage', 'Manage Roles', 'Administration', NULL),
(30, 'admin.settings', 'Manage Settings', 'Administration', NULL),
(31, 'admin.queues', 'Manage Queue Settings', 'Administration', NULL),
(32, 'admin.agents', 'Manage Agent Settings', 'Administration', NULL),
(33, 'admin.audit', 'View Audit Log', 'Administration', NULL),
(34, 'admin.agents.view', 'View Agents', 'Administration', NULL),
(35, 'admin.agents.manage', 'Manage Agents', 'Administration', NULL),
(36, 'admin.audit.view', 'View Audit Log', 'Administration', NULL),
(37, 'admin.forms.view', 'View Eval Forms', 'Administration', NULL),
(38, 'admin.forms.manage', 'Manage Eval Forms', 'Administration', NULL),
(39, 'admin.queues.view', 'View Queues', 'Administration', NULL),
(40, 'admin.queues.manage', 'Manage Queues', 'Administration', NULL),
(41, 'admin.settings.view', 'View Settings', 'Administration', NULL),
(42, 'admin.settings.edit', 'Edit Settings', 'Administration', NULL),
(43, 'quality.recordings.view', 'View Recordings', 'Quality', NULL),
(44, 'quality.recordings.listen', 'Listen Recordings', 'Quality', NULL),
(45, 'quality.recordings.download', 'Download Recordings', 'Quality', NULL),
(46, 'quality.evaluations.view', 'View Evaluations', 'Quality', NULL),
(47, 'quality.evaluations.create', 'Create Evaluations', 'Quality', NULL),
(48, 'reports.agent.export', 'Export Agent Reports', 'Reports', NULL),
(49, 'reports.queue.export', 'Export Queue Reports', 'Reports', NULL),
(50, 'reports.sla.view', 'View SLA Reports', 'Reports', NULL),
(51, 'reports.trends.view', 'View Trend Reports', 'Reports', NULL),
(52, 'scheduled-reports.view', 'View Scheduled Reports', 'Reports', NULL),
(53, 'scheduled-reports.manage', 'Manage Scheduled Reports', 'Reports', NULL),
(54, 'agent.panel.view', 'View Agent Panel', 'Agent', NULL),
(55, 'agent.panel.queue', 'Control Queue', 'Agent', NULL),
(56, 'admin.pause_causes.view', 'View Pause Causes', 'Administration', NULL),
(57, 'admin.pause_causes.manage', 'Manage Pause Causes', 'Administration', NULL);

-- --------------------------------------------
-- ROLE PERMISSIONS
-- --------------------------------------------

-- Admin gets ALL permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM permissions;

-- Supervisor permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8),
(2, 9), (2, 10), (2, 12), (2, 13), (2, 14), (2, 15), (2, 16),
(2, 17), (2, 18), (2, 20), (2, 21), (2, 22), (2, 23), (2, 24), (2, 25);

-- Agent permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 1), (3, 5), (3, 6), (3, 9), (3, 11), (3, 54), (3, 55);

-- --------------------------------------------
-- SETTINGS
-- --------------------------------------------

INSERT INTO `settings` (`category`, `setting_key`, `setting_value`, `value_type`, `description`) VALUES
-- General
('general', 'app_name', 'aReports', 'string', 'Application name'),
('general', 'app_version', '1.0.0', 'string', 'Application version'),
('general', 'timezone', 'America/New_York', 'string', 'Default timezone'),
('general', 'date_format', 'd/m/Y', 'string', 'Date format'),
('general', 'time_format', 'H:i:s', 'string', 'Time format'),
('general', 'datetime_format', 'd/m/Y H:i:s', 'string', 'DateTime format'),
('general', 'items_per_page', '25', 'int', 'Default pagination'),
('general', 'session_lifetime', '7200', 'int', 'Session lifetime in seconds'),
-- AMI
('ami', 'host', '127.0.0.1', 'string', 'AMI host'),
('ami', 'port', '5038', 'int', 'AMI port'),
('ami', 'username', 'areports', 'string', 'AMI username'),
('ami', 'secret', '', 'string', 'AMI secret'),
('ami', 'connect_timeout', '5', 'int', 'Connection timeout'),
('ami', 'read_timeout', '10', 'int', 'Read timeout'),
-- Email
('email', 'enabled', '0', 'bool', 'Enable email notifications'),
('email', 'smtp_host', '', 'string', 'SMTP server'),
('email', 'smtp_port', '587', 'int', 'SMTP port'),
('email', 'smtp_user', '', 'string', 'SMTP username'),
('email', 'smtp_pass', '', 'string', 'SMTP password'),
('email', 'smtp_encryption', 'tls', 'string', 'SMTP encryption'),
('email', 'from_address', 'noreply@example.com', 'string', 'From address'),
('email', 'from_name', 'aReports', 'string', 'From name'),
-- SLA
('sla', 'default_threshold', '60', 'int', 'Default SLA threshold'),
('sla', 'warning_percentage', '80', 'int', 'Warning percentage'),
('sla', 'critical_percentage', '100', 'int', 'Critical percentage'),
-- Recordings
('recordings', 'enabled', '1', 'bool', 'Enable recording playback'),
('recordings', 'path', '/var/spool/asterisk/monitor', 'string', 'Recordings directory'),
('recordings', 'format', 'wav', 'string', 'Recording format'),
('recordings', 'web_path', '/areports/recordings', 'string', 'Web path'),
-- Realtime
('realtime', 'refresh_interval', '5000', 'int', 'Refresh interval in ms'),
('realtime', 'websocket_enabled', '1', 'bool', 'Enable WebSocket'),
('realtime', 'websocket_port', '8080', 'int', 'WebSocket port'),
-- Security
('security', 'max_login_attempts', '5', 'int', 'Max failed login attempts'),
('security', 'lockout_duration', '900', 'int', 'Lockout duration in seconds'),
('security', 'password_min_length', '8', 'int', 'Min password length'),
('security', 'session_regenerate', '1', 'bool', 'Regenerate session ID');

-- --------------------------------------------
-- PAUSE CAUSES
-- --------------------------------------------

INSERT INTO `pause_causes` (`code`, `name`, `description`, `is_active`, `sort_order`) VALUES
('break', 'Break', 'Short break', 1, 1),
('lunch', 'Lunch', 'Lunch break', 1, 2),
('meeting', 'Meeting', 'In a meeting', 1, 3),
('training', 'Training', 'Training session', 1, 4),
('coaching', 'Coaching', 'Coaching session', 1, 5),
('admin', 'Admin Work', 'Administrative tasks', 1, 6),
('technical', 'Technical Issue', 'Technical problems', 1, 7),
('personal', 'Personal', 'Personal reason', 1, 8),
('other', 'Other', 'Other reason', 1, 9);

-- --------------------------------------------
-- DEFAULT ADMIN USER (password: Admin@123)
-- --------------------------------------------

INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role_id`, `is_active`) VALUES
('admin', 'admin@localhost', '$2y$10$8tGIH4Xo7mVrPEqKvYPT4.QF9K5LmBKVxj7xQ2f0Jx9Qz6M0jP3Hy', 'Admin', 'User', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
