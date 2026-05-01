-- =============================================================
-- Help Desk — Knowledge Base Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(75, 17, 'Title',    'title',    'text',     1, 1, 1, 1, NULL, 0),
(76, 17, 'Category', 'category', 'dropdown', 1, 0, 1, 1, '{"choices":["Getting Started","Troubleshooting","Billing","Security"]}', 1),
(77, 17, 'Content',  'content',  'textarea', 1, 0, 1, 0, NULL, 2);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(420, 5, 17, 1, '{"title":"How to reset your password","category":"Getting Started","content":"Navigate to the login page and click Forgot Password..."}'),
(421, 5, 17, 1, '{"title":"VPN Setup Guide","category":"Troubleshooting","content":"Download the VPN client from the IT portal and enter your credentials..."}'),
(422, 5, 17, 1, '{"title":"Understanding your invoice","category":"Billing","content":"Your invoice is generated on the 1st of each month..."}');
