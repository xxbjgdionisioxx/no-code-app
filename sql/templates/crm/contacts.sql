-- =============================================================
-- CRM — Contacts Module
-- Fields: Full Name, Email, Phone, Status
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(10, 3, 'Full Name',  'full_name', 'text',     1, 0, 1, 1, NULL, 0),
(11, 3, 'Email',      'email',     'text',     1, 1, 1, 1, NULL, 1),
(12, 3, 'Phone',      'phone',     'text',     0, 0, 1, 1, NULL, 2),
(13, 3, 'Status',     'status',    'dropdown', 1, 0, 1, 1, '{"choices":["New Lead","Contacted","Qualified","Customer","Lost"]}', 3),
(14, 3, 'Notes',      'notes',     'textarea', 0, 0, 0, 0, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(110, 2, 3, 1, '{"full_name":"John Doe","email":"john@acmecorp.com","phone":"+1 555-0101","status":"Customer","notes":"Longtime client, VIP tier."}'),
(111, 2, 3, 1, '{"full_name":"Jane Smith","email":"jane@globex.com","phone":"+1 555-0202","status":"Qualified","notes":"Interested in enterprise plan."}'),
(112, 2, 3, 1, '{"full_name":"Tony Stark","email":"tony@stark.com","phone":"+1 555-0303","status":"New Lead","notes":"Met at TechConf 2025."}'),
(113, 2, 3, 1, '{"full_name":"Maria Garcia","email":"maria@healthco.com","phone":"+1 555-0404","status":"Contacted","notes":"Sent demo video."}'),
(114, 2, 3, 1, '{"full_name":"Dave Lee","email":"dave@retailmax.com","phone":"+1 555-0505","status":"Lost","notes":"Chose competitor."}');
