-- =============================================================
-- Project Tracker — Master Seeder
-- Modules: Projects, Tasks, Milestones, Time Logs
-- =============================================================
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(5,  3, 'Projects',   'projects',   'High-level project tracking',       'bi-folder',       0),
(6,  3, 'Tasks',      'tasks',      'Individual project tasks',           'bi-check2-square',1),
(12, 3, 'Milestones', 'milestones', 'Key project markers and deadlines',  'bi-flag',         2),
(13, 3, 'Time Logs',  'time_logs',  'Hours logged per task',              'bi-clock-history',3);

-- Projects fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(30, 5, 'Project Name', 'name',       'text',     1, 1, 1, 1, NULL, 0),
(31, 5, 'Status',       'status',     'dropdown', 1, 0, 1, 1, '{"choices":["Planning","Active","On Hold","Completed"]}', 1),
(32, 5, 'Budget',       'budget',     'number',   0, 0, 0, 1, NULL, 2),
(33, 5, 'Start Date',   'start_date', 'date',     0, 0, 0, 1, NULL, 3),
(34, 5, 'Due Date',     'due_date',   'date',     0, 0, 0, 1, NULL, 4);

-- Tasks fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(35, 6, 'Task Name', 'name',     'text',     1, 0, 1, 1, NULL, 0),
(36, 6, 'Priority',  'priority', 'dropdown', 1, 0, 1, 1, '{"choices":["Low","Normal","High","Critical"]}', 1),
(37, 6, 'Status',    'status',   'dropdown', 1, 0, 1, 1, '{"choices":["Backlog","In Progress","Review","Done"]}', 2),
(38, 6, 'Due Date',  'due_date', 'date',     0, 0, 0, 1, NULL, 3),
(39, 6, 'Assignee',  'assignee', 'text',     0, 0, 1, 1, NULL, 4);

-- Milestones fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(40, 12, 'Milestone', 'name',   'text',     1, 0, 1, 1, NULL, 0),
(41, 12, 'Date',      'date',   'date',     1, 0, 0, 1, NULL, 1),
(42, 12, 'Status',    'status', 'dropdown', 1, 0, 1, 1, '{"choices":["Upcoming","Reached","Missed"]}', 2);

-- Time Logs fields
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(45, 13, 'Task',      'task',  'text',   1, 0, 1, 1, NULL, 0),
(46, 13, 'Hours',     'hours', 'number', 1, 0, 0, 1, NULL, 1),
(47, 13, 'Log Date',  'date',  'date',   1, 0, 0, 1, NULL, 2),
(48, 13, 'Developer', 'dev',   'text',   0, 0, 1, 1, NULL, 3);

-- Sample Records: Projects
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(200, 3, 5, 1, '{"name":"Website Redesign","status":"Active","budget":"15000","start_date":"2025-04-01","due_date":"2025-07-31"}'),
(201, 3, 5, 1, '{"name":"Mobile App Q4","status":"Planning","budget":"40000","start_date":"2025-07-01","due_date":"2025-12-31"}'),
(202, 3, 5, 1, '{"name":"Cloud Migration","status":"Completed","budget":"25000","start_date":"2025-01-01","due_date":"2025-04-30"}'),
(203, 3, 5, 1, '{"name":"Data Warehouse","status":"On Hold","budget":"30000","start_date":"2025-05-01","due_date":"2025-09-30"}');

-- Sample Records: Tasks
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(210, 3, 6, 1, '{"name":"Design Homepage","priority":"High","status":"In Progress","due_date":"2025-06-15","assignee":"Alice"}'),
(211, 3, 6, 1, '{"name":"API Integration","priority":"Critical","status":"Backlog","due_date":"2025-06-20","assignee":"Bob"}'),
(212, 3, 6, 1, '{"name":"User Testing","priority":"Normal","status":"Review","due_date":"2025-07-01","assignee":"Carol"}'),
(213, 3, 6, 1, '{"name":"Setup CI/CD","priority":"High","status":"Done","due_date":"2025-05-30","assignee":"Bob"}'),
(214, 3, 6, 1, '{"name":"Write Docs","priority":"Low","status":"Backlog","due_date":"2025-07-15","assignee":"Alice"}');

-- Sample Records: Milestones
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(220, 3, 12, 1, '{"name":"Design Approved","date":"2025-05-15","status":"Reached"}'),
(221, 3, 12, 1, '{"name":"Beta Launch","date":"2025-06-30","status":"Upcoming"}'),
(222, 3, 12, 1, '{"name":"Production Go-Live","date":"2025-07-31","status":"Upcoming"}');

-- Sample Records: Time Logs
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(230, 3, 13, 1, '{"task":"Homepage Design","hours":"4","date":"2025-06-02","dev":"Alice"}'),
(231, 3, 13, 1, '{"task":"API Integration","hours":"6","date":"2025-06-03","dev":"Bob"}'),
(232, 3, 13, 1, '{"task":"Unit Tests","hours":"3","date":"2025-06-04","dev":"Carol"}'),
(233, 3, 13, 1, '{"task":"CI/CD Setup","hours":"8","date":"2025-05-30","dev":"Bob"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(20, 3, 1, 'Active Projects',  'count',     5,  NULL, '#3b82f6', 3),
(21, 3, 1, 'Total Tasks',      'count',     6,  NULL, '#10b981', 3),
(22, 3, 1, 'Billable Hours',   'sum',       13, 46,   '#f59e0b', 3),
(23, 3, 1, 'Milestones Met',   'count',     12, NULL, '#8b5cf6', 3),
(24, 3, 1, 'Task Priorities',  'pie_chart', 6,  36,   '#ec4899', 6),
(25, 3, 1, 'Project Status',   'bar_chart', 5,  31,   '#3b82f6', 6);

SET FOREIGN_KEY_CHECKS = 1;
