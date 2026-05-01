-- Help Desk — Master Seeder
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(8,  5, 'Tickets',        'tickets',  'Support requests',        'bi-ticket-perforated', 0),
(17, 5, 'Knowledge Base', 'kb',       'Internal help articles',  'bi-book',              1),
(19, 5, 'Feedback',       'feedback', 'Customer satisfaction',   'bi-star',              2);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(70, 8, 'Subject',       'subject',  'text',     1, 0, 1, 1, NULL, 0),
(71, 8, 'Status',        'status',   'dropdown', 1, 0, 1, 1, '{"choices":["New","Open","In Progress","Resolved","Closed"]}', 1),
(72, 8, 'Priority',      'priority', 'dropdown', 1, 0, 1, 1, '{"choices":["Low","Normal","High","Urgent"]}', 2),
(73, 8, 'Category',      'category', 'dropdown', 1, 0, 1, 1, '{"choices":["Technical","Billing","General","Feature Request"]}', 3),
(74, 8, 'Customer',      'customer', 'text',     1, 0, 1, 1, NULL, 4);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(75, 17, 'Title',    'title',    'text',     1, 1, 1, 1, NULL, 0),
(76, 17, 'Category', 'category', 'dropdown', 1, 0, 1, 1, '{"choices":["Getting Started","Troubleshooting","Billing","Security"]}', 1),
(77, 17, 'Content',  'content',  'textarea', 1, 0, 1, 0, NULL, 2);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(80, 19, 'Ticket Ref', 'ticket_ref', 'text',     1, 0, 1, 1, NULL, 0),
(81, 19, 'Rating',     'rating',     'number',   1, 0, 0, 1, NULL, 1),
(82, 19, 'Comments',   'comments',   'textarea', 0, 0, 0, 0, NULL, 2),
(83, 19, 'Customer',   'customer',   'text',     0, 0, 1, 1, NULL, 3);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(410, 5, 8, 1, '{"subject":"Cannot login to portal","status":"In Progress","priority":"High","category":"Technical","customer":"John Doe"}'),
(411, 5, 8, 1, '{"subject":"New mouse requested","status":"New","priority":"Low","category":"General","customer":"Bob Miller"}'),
(412, 5, 8, 1, '{"subject":"Invoice discrepancy","status":"Open","priority":"Urgent","category":"Billing","customer":"Jane Smith"}'),
(413, 5, 8, 1, '{"subject":"Add dark mode feature","status":"Resolved","priority":"Normal","category":"Feature Request","customer":"Carol White"}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(420, 5, 17, 1, '{"title":"How to reset your password","category":"Getting Started","content":"Go to login page, click Forgot Password."}'),
(421, 5, 17, 1, '{"title":"VPN Setup Guide","category":"Troubleshooting","content":"Download VPN client from IT portal."}'),
(422, 5, 17, 1, '{"title":"Understanding your invoice","category":"Billing","content":"Invoices are generated on the 1st of each month."}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(430, 5, 19, 1, '{"ticket_ref":"TKT-001","rating":"5","comments":"Very quick resolution!","customer":"John Doe"}'),
(431, 5, 19, 1, '{"ticket_ref":"TKT-003","rating":"3","comments":"Took a bit long but resolved.","customer":"Jane Smith"}');

INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(40, 5, 1, 'New Tickets',        'count',     8,  NULL, '#8b5cf6', 3),
(41, 5, 1, 'Avg Rating',         'average',   19, 81,   '#f59e0b', 3),
(42, 5, 1, 'KB Articles',        'count',     17, NULL, '#10b981', 3),
(43, 5, 1, 'Feedback Received',  'count',     19, NULL, '#3b82f6', 3),
(44, 5, 1, 'Tickets by Priority','bar_chart', 8,  72,   '#ef4444', 6),
(45, 5, 1, 'Status Overview',    'pie_chart', 8,  71,   '#8b5cf6', 6);

SET FOREIGN_KEY_CHECKS = 1;
