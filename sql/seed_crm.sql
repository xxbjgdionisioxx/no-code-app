-- =============================================================
-- Sales CRM — Master Seeder
-- Loads all modules in order from sql/templates/crm/
-- =============================================================
SET FOREIGN_KEY_CHECKS = 0;

-- Modules (load order matters: modules before fields before records)
INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(3,  2, 'Contacts',  'contacts',  'Customer contact information',     'bi-person-badge',    0),
(4,  2, 'Deals',     'deals',     'Sales opportunities and pipeline',  'bi-currency-dollar', 1),
(10, 2, 'Companies', 'companies', 'B2B client organizations',          'bi-building',        2),
(11, 2, 'Tasks',     'tasks',     'Sales follow-ups and actions',      'bi-list-check',      3);

-- Contacts fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(10, 3, 'Full Name', 'full_name', 'text',     1, 0, 1, 1, NULL, 0),
(11, 3, 'Email',     'email',     'text',     1, 1, 1, 1, NULL, 1),
(12, 3, 'Phone',     'phone',     'text',     0, 0, 1, 1, NULL, 2),
(13, 3, 'Status',    'status',    'dropdown', 1, 0, 1, 1, '{"choices":["New Lead","Contacted","Qualified","Customer","Lost"]}', 3),
(14, 3, 'Notes',     'notes',     'textarea', 0, 0, 0, 0, NULL, 4);

-- Deals fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(15, 4, 'Deal Name',    'deal_name',    'text',     1, 0, 1, 1, NULL, 0),
(16, 4, 'Amount',       'amount',       'number',   1, 0, 0, 1, NULL, 1),
(17, 4, 'Stage',        'stage',        'dropdown', 1, 0, 1, 1, '{"choices":["Discovery","Proposal","Negotiation","Closed Won","Closed Lost"]}', 2),
(18, 4, 'Closing Date', 'closing_date', 'date',     0, 0, 0, 1, NULL, 3),
(19, 4, 'Contact Name', 'contact',      'text',     0, 0, 1, 1, NULL, 4);

-- Companies fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(20, 10, 'Company Name', 'name',     'text',     1, 1, 1, 1, NULL, 0),
(21, 10, 'Industry',     'industry', 'dropdown', 0, 0, 1, 1, '{"choices":["Technology","Finance","Healthcare","Manufacturing","Retail","Other"]}', 1),
(22, 10, 'Website',      'website',  'text',     0, 0, 0, 1, NULL, 2),
(23, 10, 'Employees',    'size',     'dropdown', 0, 0, 1, 1, '{"choices":["1-10","11-50","51-200","201-1000","1000+"]}', 3);

-- Tasks fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(25, 11, 'Subject',     'subject',     'text',     1, 0, 1, 1, NULL, 0),
(26, 11, 'Due Date',    'due_date',    'date',     1, 0, 0, 1, NULL, 1),
(27, 11, 'Priority',    'priority',    'dropdown', 1, 0, 1, 1, '{"choices":["Low","Medium","High"]}', 2),
(28, 11, 'Status',      'status',      'dropdown', 1, 0, 1, 1, '{"choices":["To Do","In Progress","Done"]}', 3),
(29, 11, 'Assigned To', 'assigned_to', 'text',     0, 0, 1, 1, NULL, 4);

-- Sample Records: Companies
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(130, 2, 10, 1, '{"name":"Acme Corp","industry":"Technology","website":"acmecorp.com","size":"201-1000"}'),
(131, 2, 10, 1, '{"name":"Globex Inc","industry":"Finance","website":"globex.com","size":"51-200"}'),
(132, 2, 10, 1, '{"name":"Stark Industries","industry":"Manufacturing","website":"stark.com","size":"1000+"}'),
(133, 2, 10, 1, '{"name":"Health Co","industry":"Healthcare","website":"healthco.com","size":"11-50"}');

-- Sample Records: Contacts
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(110, 2, 3, 1, '{"full_name":"John Doe","email":"john@acmecorp.com","phone":"+1 555-0101","status":"Customer","notes":"Longtime client, VIP tier."}'),
(111, 2, 3, 1, '{"full_name":"Jane Smith","email":"jane@globex.com","phone":"+1 555-0202","status":"Qualified","notes":"Interested in enterprise plan."}'),
(112, 2, 3, 1, '{"full_name":"Tony Stark","email":"tony@stark.com","phone":"+1 555-0303","status":"New Lead","notes":"Met at TechConf 2025."}'),
(113, 2, 3, 1, '{"full_name":"Maria Garcia","email":"maria@healthco.com","phone":"+1 555-0404","status":"Contacted","notes":"Sent demo video."}'),
(114, 2, 3, 1, '{"full_name":"Dave Lee","email":"dave@retailmax.com","phone":"+1 555-0505","status":"Lost","notes":"Chose competitor."}');

-- Sample Records: Deals
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(120, 2, 4, 1, '{"deal_name":"Server Expansion","amount":"50000","stage":"Proposal","closing_date":"2025-07-01","contact":"John Doe"}'),
(121, 2, 4, 1, '{"deal_name":"Software Licenses","amount":"12000","stage":"Negotiation","closing_date":"2025-06-15","contact":"Jane Smith"}'),
(122, 2, 4, 1, '{"deal_name":"Consulting Retainer","amount":"8500","stage":"Closed Won","closing_date":"2025-05-10","contact":"Maria Garcia"}'),
(123, 2, 4, 1, '{"deal_name":"Annual SaaS Plan","amount":"36000","stage":"Discovery","closing_date":"2025-08-01","contact":"Tony Stark"}'),
(124, 2, 4, 1, '{"deal_name":"Hardware Refresh","amount":"22000","stage":"Closed Lost","closing_date":"2025-04-20","contact":"Dave Lee"}');

-- Sample Records: Tasks
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(140, 2, 11, 1, '{"subject":"Send demo to Jane","due_date":"2025-06-05","priority":"High","status":"To Do","assigned_to":"Sales Team"}'),
(141, 2, 11, 1, '{"subject":"Follow up with Acme","due_date":"2025-06-10","priority":"Medium","status":"In Progress","assigned_to":"John"}'),
(142, 2, 11, 1, '{"subject":"Prepare proposal doc","due_date":"2025-06-12","priority":"High","status":"To Do","assigned_to":"Maria"}'),
(143, 2, 11, 1, '{"subject":"Close Stark deal","due_date":"2025-07-01","priority":"High","status":"In Progress","assigned_to":"Sales Team"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(10, 2, 1, 'Total Contacts',    'count',     3,  NULL, '#f59e0b', 3),
(11, 2, 1, 'Pipeline Value',    'sum',       4,  16,   '#10b981', 3),
(12, 2, 1, 'Active Companies',  'count',     10, NULL, '#6366f1', 3),
(13, 2, 1, 'Open Tasks',        'count',     11, NULL, '#3b82f6', 3),
(14, 2, 1, 'Deals by Stage',    'bar_chart', 4,  17,   '#f59e0b', 6),
(15, 2, 1, 'Lead Status Mix',   'pie_chart', 3,  13,   '#ec4899', 6);

SET FOREIGN_KEY_CHECKS = 1;
