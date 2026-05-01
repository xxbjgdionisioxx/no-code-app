-- HR Portal — Master Seeder
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(7, 4, 'Employees', 'employees', 'Staff directory', 'bi-people', 0),
(14, 4, 'Departments', 'departments', 'Organization units', 'bi-diagram-3', 1),
(15, 4, 'Leaves', 'leaves', 'Time-off requests', 'bi-calendar-event', 2);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(50, 7, 'Full Name',  'name',      'text',     1, 0, 1, 1, NULL, 0),
(51, 7, 'Department', 'dept',      'dropdown', 1, 0, 1, 1, '{"choices":["IT","Sales","Marketing","HR","Finance","Operations"]}', 1),
(52, 7, 'Job Title',  'job_title', 'text',     1, 0, 1, 1, NULL, 2),
(53, 7, 'Salary',     'salary',    'number',   0, 0, 0, 0, NULL, 3),
(54, 7, 'Hire Date',  'hire_date', 'date',     1, 0, 0, 1, NULL, 4);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(55, 14, 'Dept Name', 'name',    'text',   1, 1, 1, 1, NULL, 0),
(56, 14, 'Manager',   'manager', 'text',   0, 0, 1, 1, NULL, 1),
(57, 14, 'Budget',    'budget',  'number', 0, 0, 0, 1, NULL, 2);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(60, 15, 'Employee',   'employee',   'text',     1, 0, 1, 1, NULL, 0),
(61, 15, 'Leave Type', 'type',       'dropdown', 1, 0, 1, 1, '{"choices":["Sick","Vacation","Personal","Bereavement","Parental"]}', 1),
(62, 15, 'Start Date', 'start_date', 'date',     1, 0, 0, 1, NULL, 2),
(63, 15, 'Days',       'days',       'number',   1, 0, 0, 1, NULL, 3),
(64, 15, 'Status',     'status',     'dropdown', 1, 0, 1, 1, '{"choices":["Pending","Approved","Rejected"]}', 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(320, 4, 14, 1, '{"name":"Information Technology","manager":"Alice Johnson","budget":"500000"}'),
(321, 4, 14, 1, '{"name":"Global Sales","manager":"Bob Miller","budget":"350000"}'),
(322, 4, 14, 1, '{"name":"Human Resources","manager":"Carol White","budget":"200000"}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(310, 4, 7, 1, '{"name":"Alice Johnson","dept":"IT","job_title":"Senior Developer","salary":"85000","hire_date":"2022-03-15"}'),
(311, 4, 7, 1, '{"name":"Bob Miller","dept":"Sales","job_title":"Sales Executive","salary":"62000","hire_date":"2021-07-01"}'),
(312, 4, 7, 1, '{"name":"Carol White","dept":"HR","job_title":"HR Manager","salary":"71000","hire_date":"2020-01-10"}'),
(313, 4, 7, 1, '{"name":"Dave Brown","dept":"Finance","job_title":"Accountant","salary":"68000","hire_date":"2023-09-05"}'),
(314, 4, 7, 1, '{"name":"Eve Davis","dept":"Marketing","job_title":"Brand Manager","salary":"74000","hire_date":"2022-11-20"}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(330, 4, 15, 1, '{"employee":"Alice Johnson","type":"Vacation","start_date":"2025-07-01","days":"10","status":"Approved"}'),
(331, 4, 15, 1, '{"employee":"Bob Miller","type":"Sick","start_date":"2025-06-10","days":"3","status":"Approved"}'),
(332, 4, 15, 1, '{"employee":"Eve Davis","type":"Personal","start_date":"2025-06-20","days":"2","status":"Pending"}');

INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(30, 4, 1, 'Total Headcount',   'count',     7,  NULL, '#ec4899', 3),
(31, 4, 1, 'Departments',       'count',     14, NULL, '#3b82f6', 3),
(32, 4, 1, 'Open Leaves',       'count',     15, NULL, '#f59e0b', 3),
(33, 4, 1, 'Monthly Payroll',   'sum',       7,  53,   '#10b981', 3),
(34, 4, 1, 'Dept Distribution', 'pie_chart', 7,  51,   '#ec4899', 6),
(35, 4, 1, 'Leave by Type',     'bar_chart', 15, 61,   '#f43f5e', 6);

SET FOREIGN_KEY_CHECKS = 1;
