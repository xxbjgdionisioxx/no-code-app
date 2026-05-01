-- =============================================================
-- Project Tracker — Projects Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(30, 5, 'Project Name', 'name',       'text',     1, 1, 1, 1, NULL, 0),
(31, 5, 'Status',       'status',     'dropdown', 1, 0, 1, 1, '{"choices":["Planning","Active","On Hold","Completed"]}', 1),
(32, 5, 'Budget',       'budget',     'number',   0, 0, 0, 1, NULL, 2),
(33, 5, 'Start Date',   'start_date', 'date',     0, 0, 0, 1, NULL, 3),
(34, 5, 'Due Date',     'due_date',   'date',     0, 0, 0, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(200, 3, 5, 1, '{"name":"Website Redesign","status":"Active","budget":"15000","start_date":"2025-04-01","due_date":"2025-07-31"}'),
(201, 3, 5, 1, '{"name":"Mobile App Q4","status":"Planning","budget":"40000","start_date":"2025-07-01","due_date":"2025-12-31"}'),
(202, 3, 5, 1, '{"name":"Cloud Migration","status":"Completed","budget":"25000","start_date":"2025-01-01","due_date":"2025-04-30"}'),
(203, 3, 5, 1, '{"name":"Data Warehouse","status":"On Hold","budget":"30000","start_date":"2025-05-01","due_date":"2025-09-30"}');
