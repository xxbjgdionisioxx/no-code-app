-- =============================================================
-- Fleet Manager — Fuel Logs Module + Widgets
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(100, 21, 'Vehicle Plate', 'vehicle', 'text',   1, 0, 1, 1, NULL, 0),
(101, 21, 'Liters',        'liters',  'number', 1, 0, 0, 1, NULL, 1),
(102, 21, 'Total Price',   'price',   'number', 1, 0, 0, 1, NULL, 2),
(103, 21, 'Log Date',      'date',    'date',   1, 0, 0, 1, NULL, 3),
(104, 21, 'Odometer',      'odo',     'number', 0, 0, 0, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(520, 6, 21, 1, '{"vehicle":"ABC-1234","liters":"40","price":"62","date":"2025-06-01","odo":"45600"}'),
(521, 6, 21, 1, '{"vehicle":"VAN-9988","liters":"65","price":"101","date":"2025-06-02","odo":"82400"}'),
(522, 6, 21, 1, '{"vehicle":"SUV-2277","liters":"45","price":"70","date":"2025-06-03","odo":"31800"}');

-- Dashboard Widgets
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(50, 6, 1, 'Fleet Size',       'count',     9,  NULL, '#14b8a6', 3),
(51, 6, 1, 'Fuel Spend',       'sum',       21, 102,  '#10b981', 3),
(52, 6, 1, 'Maint. Costs',     'sum',       20, 96,   '#f43f5e', 3),
(53, 6, 1, 'Avg Fuel Cost',    'average',   21, 102,  '#f59e0b', 3),
(54, 6, 1, 'Vehicle Types',    'pie_chart', 9,  91,   '#14b8a6', 6),
(55, 6, 1, 'Maint. by Type',   'bar_chart', 20, 98,   '#2dd4bf', 6);
