-- =============================================================
-- Project Tracker — Tasks Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(35, 6, 'Task Name',   'name',      'text',     1, 0, 1, 1, NULL, 0),
(36, 6, 'Priority',    'priority',  'dropdown', 1, 0, 1, 1, '{"choices":["Low","Normal","High","Critical"]}', 1),
(37, 6, 'Status',      'status',    'dropdown', 1, 0, 1, 1, '{"choices":["Backlog","In Progress","Review","Done"]}', 2),
(38, 6, 'Due Date',    'due_date',  'date',     0, 0, 0, 1, NULL, 3),
(39, 6, 'Assignee',    'assignee',  'text',     0, 0, 1, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(210, 3, 6, 1, '{"name":"Design Homepage","priority":"High","status":"In Progress","due_date":"2025-06-15","assignee":"Alice"}'),
(211, 3, 6, 1, '{"name":"API Integration","priority":"Critical","status":"Backlog","due_date":"2025-06-20","assignee":"Bob"}'),
(212, 3, 6, 1, '{"name":"User Testing","priority":"Normal","status":"Review","due_date":"2025-07-01","assignee":"Carol"}'),
(213, 3, 6, 1, '{"name":"Setup CI/CD","priority":"High","status":"Done","due_date":"2025-05-30","assignee":"Bob"}'),
(214, 3, 6, 1, '{"name":"Write Docs","priority":"Low","status":"Backlog","due_date":"2025-07-15","assignee":"Alice"}');
