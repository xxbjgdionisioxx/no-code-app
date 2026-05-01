-- =============================================================
-- Help Desk — Tickets Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(70, 8, 'Subject',       'subject',   'text',     1, 0, 1, 1, NULL, 0),
(71, 8, 'Status',        'status',    'dropdown', 1, 0, 1, 1, '{"choices":["New","Open","In Progress","Resolved","Closed"]}', 1),
(72, 8, 'Priority',      'priority',  'dropdown', 1, 0, 1, 1, '{"choices":["Low","Normal","High","Urgent"]}', 2),
(73, 8, 'Category',      'category',  'dropdown', 1, 0, 1, 1, '{"choices":["Technical","Billing","General","Feature Request"]}', 3),
(74, 8, 'Customer Name', 'customer',  'text',     1, 0, 1, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(410, 5, 8, 1, '{"subject":"Cannot login to portal","status":"In Progress","priority":"High","category":"Technical","customer":"John Doe"}'),
(411, 5, 8, 1, '{"subject":"New mouse requested","status":"New","priority":"Low","category":"General","customer":"Bob Miller"}'),
(412, 5, 8, 1, '{"subject":"Invoice discrepancy","status":"Open","priority":"Urgent","category":"Billing","customer":"Jane Smith"}'),
(413, 5, 8, 1, '{"subject":"Add dark mode feature","status":"Resolved","priority":"Normal","category":"Feature Request","customer":"Carol White"}'),
(414, 5, 8, 1, '{"subject":"Slow VPN connection","status":"Closed","priority":"Normal","category":"Technical","customer":"Dave Brown"}');
