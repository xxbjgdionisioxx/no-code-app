-- =============================================================
-- CRM — Deals Module
-- Fields: Deal Name, Amount, Stage, Closing Date, Contact
-- =============================================================
INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, options, sort_order) VALUES
(15, 4, 'Deal Name',    'deal_name',    'text',     1, 0, 1, 1, NULL, 0),
(16, 4, 'Amount',       'amount',       'number',   1, 0, 0, 1, NULL, 1),
(17, 4, 'Stage',        'stage',        'dropdown', 1, 0, 1, 1, '{"choices":["Discovery","Proposal","Negotiation","Closed Won","Closed Lost"]}', 2),
(18, 4, 'Closing Date', 'closing_date', 'date',     0, 0, 0, 1, NULL, 3),
(19, 4, 'Contact Name', 'contact',      'text',     0, 0, 1, 1, NULL, 4);

INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(120, 2, 4, 1, '{"deal_name":"Server Expansion","amount":"50000","stage":"Proposal","closing_date":"2025-07-01","contact":"John Doe"}'),
(121, 2, 4, 1, '{"deal_name":"Software Licenses","amount":"12000","stage":"Negotiation","closing_date":"2025-06-15","contact":"Jane Smith"}'),
(122, 2, 4, 1, '{"deal_name":"Consulting Retainer","amount":"8500","stage":"Closed Won","closing_date":"2025-05-10","contact":"Maria Garcia"}'),
(123, 2, 4, 1, '{"deal_name":"Annual SaaS Plan","amount":"36000","stage":"Discovery","closing_date":"2025-08-01","contact":"Tony Stark"}'),
(124, 2, 4, 1, '{"deal_name":"Hardware Refresh","amount":"22000","stage":"Closed Lost","closing_date":"2025-04-20","contact":"Dave Lee"}');
