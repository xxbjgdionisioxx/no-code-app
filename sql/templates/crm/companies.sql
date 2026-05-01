-- =============================================================
-- CRM — Companies Module
-- Fields: Company Name, Industry, Website, Size
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(20, 10, 'Company Name', 'name',     'text',     1, 1, 1, 1, NULL, 0),
(21, 10, 'Industry',     'industry', 'dropdown', 0, 0, 1, 1, '{"choices":["Technology","Finance","Healthcare","Manufacturing","Retail","Other"]}', 1),
(22, 10, 'Website',      'website',  'text',     0, 0, 0, 1, NULL, 2),
(23, 10, 'Employees',    'size',     'dropdown', 0, 0, 1, 1, '{"choices":["1-10","11-50","51-200","201-1000","1000+"]}', 3);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(130, 2, 10, 1, '{"name":"Acme Corp","industry":"Technology","website":"acmecorp.com","size":"201-1000"}'),
(131, 2, 10, 1, '{"name":"Globex Inc","industry":"Finance","website":"globex.com","size":"51-200"}'),
(132, 2, 10, 1, '{"name":"Stark Industries","industry":"Manufacturing","website":"stark.com","size":"1000+"}'),
(133, 2, 10, 1, '{"name":"Health Co","industry":"Healthcare","website":"healthco.com","size":"11-50"}');
