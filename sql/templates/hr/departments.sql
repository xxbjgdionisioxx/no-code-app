-- =============================================================
-- HR Portal — Departments Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(55, 14, 'Department Name', 'name',    'text', 1, 1, 1, 1, NULL, 0),
(56, 14, 'Manager',         'manager', 'text', 0, 0, 1, 1, NULL, 1),
(57, 14, 'Budget',          'budget',  'number', 0, 0, 0, 1, NULL, 2);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(320, 4, 14, 1, '{"name":"Information Technology","manager":"Alice Johnson","budget":"500000"}'),
(321, 4, 14, 1, '{"name":"Global Sales","manager":"Bob Miller","budget":"350000"}'),
(322, 4, 14, 1, '{"name":"Human Resources","manager":"Carol White","budget":"200000"}'),
(323, 4, 14, 1, '{"name":"Finance","manager":"Dave Brown","budget":"250000"}');
