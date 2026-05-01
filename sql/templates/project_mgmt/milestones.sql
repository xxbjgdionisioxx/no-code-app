-- =============================================================
-- Project Tracker — Milestones Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(40, 12, 'Milestone Name', 'name',   'text',     1, 0, 1, 1, NULL, 0),
(41, 12, 'Date',           'date',   'date',     1, 0, 0, 1, NULL, 1),
(42, 12, 'Status',         'status', 'dropdown', 1, 0, 1, 1, '{"choices":["Upcoming","Reached","Missed"]}', 2);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(220, 3, 12, 1, '{"name":"Design Approved","date":"2025-05-15","status":"Reached"}'),
(221, 3, 12, 1, '{"name":"Beta Launch","date":"2025-06-30","status":"Upcoming"}'),
(222, 3, 12, 1, '{"name":"Production Go-Live","date":"2025-07-31","status":"Upcoming"}');
