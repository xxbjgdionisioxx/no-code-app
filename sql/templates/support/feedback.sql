-- =============================================================
-- Help Desk — Feedback Module + Widgets
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(80, 19, 'Ticket Ref',  'ticket_ref', 'text',     1, 0, 1, 1, NULL, 0),
(81, 19, 'Rating',      'rating',     'number',   1, 0, 0, 1, NULL, 1),
(82, 19, 'Comments',    'comments',   'textarea', 0, 0, 0, 0, NULL, 2),
(83, 19, 'Customer',    'customer',   'text',     0, 0, 1, 1, NULL, 3);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(430, 5, 19, 1, '{"ticket_ref":"TKT-001","rating":"5","comments":"Very quick resolution!","customer":"John Doe"}'),
(431, 5, 19, 1, '{"ticket_ref":"TKT-003","rating":"3","comments":"Took a bit long but resolved.","customer":"Jane Smith"}'),
(432, 5, 19, 1, '{"ticket_ref":"TKT-004","rating":"4","comments":"Good support overall.","customer":"Carol White"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(40, 5, 1, 'New Tickets',       'count',     8,  NULL, '#8b5cf6', 3),
(41, 5, 1, 'Avg Rating',        'average',   19, 81,   '#f59e0b', 3),
(42, 5, 1, 'KB Articles',       'count',     17, NULL, '#10b981', 3),
(43, 5, 1, 'Feedback Received', 'count',     19, NULL, '#3b82f6', 3),
(44, 5, 1, 'Tickets by Priority','bar_chart',8,  72,   '#ef4444', 6),
(45, 5, 1, 'Status Overview',   'pie_chart', 8,  71,   '#8b5cf6', 6);
