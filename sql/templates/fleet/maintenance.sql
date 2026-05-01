-- =============================================================
-- Fleet Manager — Maintenance Logs Module
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(95, 20, 'Vehicle Plate', 'vehicle',      'text',     1, 0, 1, 1, NULL, 0),
(96, 20, 'Cost',          'cost',         'number',   1, 0, 0, 1, NULL, 1),
(97, 20, 'Service Date',  'service_date', 'date',     1, 0, 0, 1, NULL, 2),
(98, 20, 'Service Type',  'service_type', 'dropdown', 1, 0, 1, 1, '{"choices":["Oil Change","Tires","Brake Service","Engine Repair","General Inspection"]}', 3),
(99, 20, 'Notes',         'notes',        'textarea', 0, 0, 0, 0, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(510, 6, 20, 1, '{"vehicle":"ABC-1234","cost":"80","service_date":"2025-05-10","service_type":"Oil Change","notes":""}'),
(511, 6, 20, 1, '{"vehicle":"VAN-9988","cost":"450","service_date":"2025-04-20","service_type":"Tires","notes":"Replaced all 4 tires"}'),
(512, 6, 20, 1, '{"vehicle":"TRK-4455","cost":"1200","service_date":"2025-05-25","service_type":"Engine Repair","notes":"Coolant leak fixed"}'),
(513, 6, 20, 1, '{"vehicle":"SUV-2277","cost":"60","service_date":"2025-05-15","service_type":"General Inspection","notes":"All OK"}');
