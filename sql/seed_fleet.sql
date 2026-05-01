-- Fleet Manager — Master Seeder
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(9,  6, 'Vehicles',    'vehicles',    'Company vehicle list',   'bi-car-front',  0),
(20, 6, 'Maintenance', 'maintenance', 'Service logs & repairs', 'bi-wrench',     1),
(21, 6, 'Fuel Logs',   'fuel_logs',   'Gas and mileage logs',   'bi-fuel-pump',  2);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(90, 9, 'Plate Number',  'plate',   'text',     1, 1, 1, 1, NULL, 0),
(91, 9, 'Type',          'type',    'dropdown', 1, 0, 1, 1, '{"choices":["Sedan","SUV","Truck","Van","Electric"]}', 1),
(92, 9, 'Make/Model',    'model',   'text',     1, 0, 1, 1, NULL, 2),
(93, 9, 'Odometer (km)', 'mileage', 'number',   0, 0, 0, 1, NULL, 3),
(94, 9, 'Status',        'status',  'dropdown', 1, 0, 1, 1, '{"choices":["Active","In Maintenance","Retired"]}', 4);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(95, 20, 'Vehicle Plate', 'vehicle',      'text',     1, 0, 1, 1, NULL, 0),
(96, 20, 'Cost',          'cost',         'number',   1, 0, 0, 1, NULL, 1),
(97, 20, 'Service Date',  'service_date', 'date',     1, 0, 0, 1, NULL, 2),
(98, 20, 'Service Type',  'service_type', 'dropdown', 1, 0, 1, 1, '{"choices":["Oil Change","Tires","Brake Service","Engine Repair","General Inspection"]}', 3),
(99, 20, 'Notes',         'notes',        'textarea', 0, 0, 0, 0, NULL, 4);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(100, 21, 'Vehicle Plate', 'vehicle', 'text',   1, 0, 1, 1, NULL, 0),
(101, 21, 'Liters',        'liters',  'number', 1, 0, 0, 1, NULL, 1),
(102, 21, 'Total Price',   'price',   'number', 1, 0, 0, 1, NULL, 2),
(103, 21, 'Log Date',      'date',    'date',   1, 0, 0, 1, NULL, 3);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(500, 6, 9, 1, '{"plate":"ABC-1234","type":"Sedan","model":"Toyota Camry","mileage":"45200","status":"Active"}'),
(501, 6, 9, 1, '{"plate":"VAN-9988","type":"Van","model":"Ford Transit","mileage":"82000","status":"Active"}'),
(502, 6, 9, 1, '{"plate":"TRK-4455","type":"Truck","model":"Isuzu ELF","mileage":"112000","status":"In Maintenance"}'),
(503, 6, 9, 1, '{"plate":"SUV-2277","type":"SUV","model":"Honda CR-V","mileage":"31500","status":"Active"}'),
(504, 6, 9, 1, '{"plate":"EV-0011","type":"Electric","model":"Tesla Model 3","mileage":"18700","status":"Active"}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(510, 6, 20, 1, '{"vehicle":"ABC-1234","cost":"80","service_date":"2025-05-10","service_type":"Oil Change","notes":""}'),
(511, 6, 20, 1, '{"vehicle":"VAN-9988","cost":"450","service_date":"2025-04-20","service_type":"Tires","notes":"Replaced all 4 tires"}'),
(512, 6, 20, 1, '{"vehicle":"TRK-4455","cost":"1200","service_date":"2025-05-25","service_type":"Engine Repair","notes":"Coolant leak fixed"}');

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(520, 6, 21, 1, '{"vehicle":"ABC-1234","liters":"40","price":"62","date":"2025-06-01"}'),
(521, 6, 21, 1, '{"vehicle":"VAN-9988","liters":"65","price":"101","date":"2025-06-02"}'),
(522, 6, 21, 1, '{"vehicle":"SUV-2277","liters":"45","price":"70","date":"2025-06-03"}');

INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(50, 6, 1, 'Fleet Size',      'count',     9,  NULL, '#14b8a6', 3),
(51, 6, 1, 'Fuel Spend',      'sum',       21, 102,  '#10b981', 3),
(52, 6, 1, 'Maint. Costs',    'sum',       20, 96,   '#f43f5e', 3),
(53, 6, 1, 'Avg Fuel Cost',   'average',   21, 102,  '#f59e0b', 3),
(54, 6, 1, 'Vehicle Types',   'pie_chart', 9,  91,   '#14b8a6', 6),
(55, 6, 1, 'Maint. by Type',  'bar_chart', 20, 98,   '#2dd4bf', 6);

SET FOREIGN_KEY_CHECKS = 1;
