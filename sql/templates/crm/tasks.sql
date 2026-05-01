-- =============================================================
-- CRM — Tasks Module
-- Fields: Subject, Due Date, Priority, Status, Assigned To
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(25, 11, 'Subject',     'subject',     'text',     1, 0, 1, 1, NULL, 0),
(26, 11, 'Due Date',    'due_date',    'date',     1, 0, 0, 1, NULL, 1),
(27, 11, 'Priority',    'priority',    'dropdown', 1, 0, 1, 1, '{"choices":["Low","Medium","High"]}', 2),
(28, 11, 'Status',      'status',      'dropdown', 1, 0, 1, 1, '{"choices":["To Do","In Progress","Done"]}', 3),
(29, 11, 'Assigned To', 'assigned_to', 'text',     0, 0, 1, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(140, 2, 11, 1, '{"subject":"Send demo to Jane","due_date":"2025-06-05","priority":"High","status":"To Do","assigned_to":"Sales Team"}'),
(141, 2, 11, 1, '{"subject":"Follow up with Acme","due_date":"2025-06-10","priority":"Medium","status":"In Progress","assigned_to":"John"}'),
(142, 2, 11, 1, '{"subject":"Prepare proposal doc","due_date":"2025-06-12","priority":"High","status":"To Do","assigned_to":"Maria"}'),
(143, 2, 11, 1, '{"subject":"Close Stark deal","due_date":"2025-07-01","priority":"High","status":"In Progress","assigned_to":"Sales Team"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(10, 2, 1, 'Total Contacts',   'count',     3,  NULL, '#f59e0b', 3),
(11, 2, 1, 'Pipeline Value',   'sum',       4,  16,   '#10b981', 3),
(12, 2, 1, 'Active Companies', 'count',     10, NULL, '#6366f1', 3),
(13, 2, 1, 'Open Tasks',       'count',     11, NULL, '#3b82f6', 3),
(14, 2, 1, 'Deals by Stage',   'bar_chart', 4,  17,   '#f59e0b', 6),
(15, 2, 1, 'Lead Status Mix',  'pie_chart', 3,  13,   '#ec4899', 6);
