-- =============================================================
-- Project Tracker — Time Logs Module + Widgets
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(45, 13, 'Task',       'task',   'text',   1, 0, 1, 1, NULL, 0),
(46, 13, 'Hours',      'hours',  'number', 1, 0, 0, 1, NULL, 1),
(47, 13, 'Log Date',   'date',   'date',   1, 0, 0, 1, NULL, 2),
(48, 13, 'Developer',  'dev',    'text',   0, 0, 1, 1, NULL, 3);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(230, 3, 13, 1, '{"task":"Homepage Design","hours":"4","date":"2025-06-02","dev":"Alice"}'),
(231, 3, 13, 1, '{"task":"API Integration","hours":"6","date":"2025-06-03","dev":"Bob"}'),
(232, 3, 13, 1, '{"task":"Unit Tests","hours":"3","date":"2025-06-04","dev":"Carol"}'),
(233, 3, 13, 1, '{"task":"CI/CD Setup","hours":"8","date":"2025-05-30","dev":"Bob"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(20, 3, 1, 'Active Projects',  'count',     5,  NULL, '#3b82f6', 3),
(21, 3, 1, 'Total Tasks',      'count',     6,  NULL, '#10b981', 3),
(22, 3, 1, 'Billable Hours',   'sum',       13, 46,   '#f59e0b', 3),
(23, 3, 1, 'Milestones Met',   'count',     12, NULL, '#8b5cf6', 3),
(24, 3, 1, 'Task Priorities',  'pie_chart', 6,  36,   '#ec4899', 6),
(25, 3, 1, 'Project Status',   'bar_chart', 5,  31,   '#3b82f6', 6);
