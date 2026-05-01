-- =============================================================
-- Fleet Manager — Vehicles Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(90, 9, 'Plate Number', 'plate',     'text',     1, 1, 1, 1, NULL, 0),
(91, 9, 'Type',         'type',      'dropdown', 1, 0, 1, 1, '{"choices":["Sedan","SUV","Truck","Van","Electric"]}', 1),
(92, 9, 'Make/Model',   'model',     'text',     1, 0, 1, 1, NULL, 2),
(93, 9, 'Odometer (km)','mileage',   'number',   0, 0, 0, 1, NULL, 3),
(94, 9, 'Status',       'status',    'dropdown', 1, 0, 1, 1, '{"choices":["Active","In Maintenance","Retired"]}', 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(500, 6, 9, 1, '{"plate":"ABC-1234","type":"Sedan","model":"Toyota Camry","mileage":"45200","status":"Active"}'),
(501, 6, 9, 1, '{"plate":"VAN-9988","type":"Van","model":"Ford Transit","mileage":"82000","status":"Active"}'),
(502, 6, 9, 1, '{"plate":"TRK-4455","type":"Truck","model":"Isuzu ELF","mileage":"112000","status":"In Maintenance"}'),
(503, 6, 9, 1, '{"plate":"SUV-2277","type":"SUV","model":"Honda CR-V","mileage":"31500","status":"Active"}'),
(504, 6, 9, 1, '{"plate":"EV-0011","type":"Electric","model":"Tesla Model 3","mileage":"18700","status":"Active"}');
