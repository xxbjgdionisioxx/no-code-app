-- =============================================================
-- HR Portal — Employees Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(50, 7, 'Full Name',   'name',       'text',     1, 0, 1, 1, NULL, 0),
(51, 7, 'Department',  'dept',       'dropdown', 1, 0, 1, 1, '{"choices":["IT","Sales","Marketing","HR","Finance","Operations"]}', 1),
(52, 7, 'Job Title',   'job_title',  'text',     1, 0, 1, 1, NULL, 2),
(53, 7, 'Salary',      'salary',     'number',   0, 0, 0, 0, NULL, 3),
(54, 7, 'Hire Date',   'hire_date',  'date',     1, 0, 0, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(310, 4, 7, 1, '{"name":"Alice Johnson","dept":"IT","job_title":"Senior Developer","salary":"85000","hire_date":"2022-03-15"}'),
(311, 4, 7, 1, '{"name":"Bob Miller","dept":"Sales","job_title":"Sales Executive","salary":"62000","hire_date":"2021-07-01"}'),
(312, 4, 7, 1, '{"name":"Carol White","dept":"HR","job_title":"HR Manager","salary":"71000","hire_date":"2020-01-10"}'),
(313, 4, 7, 1, '{"name":"Dave Brown","dept":"Finance","job_title":"Accountant","salary":"68000","hire_date":"2023-09-05"}'),
(314, 4, 7, 1, '{"name":"Eve Davis","dept":"Marketing","job_title":"Brand Manager","salary":"74000","hire_date":"2022-11-20"}');
