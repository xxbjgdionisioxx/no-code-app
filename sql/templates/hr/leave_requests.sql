-- =============================================================
-- HR Portal — Leave Requests Module + Widgets
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(60, 15, 'Employee',   'employee',   'text',     1, 0, 1, 1, NULL, 0),
(61, 15, 'Leave Type', 'type',       'dropdown', 1, 0, 1, 1, '{"choices":["Sick","Vacation","Personal","Bereavement","Parental"]}', 1),
(62, 15, 'Start Date', 'start_date', 'date',     1, 0, 0, 1, NULL, 2),
(63, 15, 'Days',       'days',       'number',   1, 0, 0, 1, NULL, 3),
(64, 15, 'Status',     'status',     'dropdown', 1, 0, 1, 1, '{"choices":["Pending","Approved","Rejected"]}', 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(330, 4, 15, 1, '{"employee":"Alice Johnson","type":"Vacation","start_date":"2025-07-01","days":"10","status":"Approved"}'),
(331, 4, 15, 1, '{"employee":"Bob Miller","type":"Sick","start_date":"2025-06-10","days":"3","status":"Approved"}'),
(332, 4, 15, 1, '{"employee":"Eve Davis","type":"Personal","start_date":"2025-06-20","days":"2","status":"Pending"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(30, 4, 1, 'Total Headcount',   'count',     7,  NULL, '#ec4899', 3),
(31, 4, 1, 'Departments',       'count',     14, NULL, '#3b82f6', 3),
(32, 4, 1, 'Open Leave Reqs',   'count',     15, NULL, '#f59e0b', 3),
(33, 4, 1, 'Monthly Payroll',   'sum',       7,  53,   '#10b981', 3),
(34, 4, 1, 'Dept Distribution', 'pie_chart', 7,  51,   '#ec4899', 6),
(35, 4, 1, 'Leave by Type',     'bar_chart', 15, 61,   '#f43f5e', 6);
