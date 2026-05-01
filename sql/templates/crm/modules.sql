-- =============================================================
-- CRM: app.sql — App definition only
-- Modules: contacts.sql, deals.sql, companies.sql, tasks.sql
-- =============================================================
INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(3, 2, 'Contacts', 'contacts', 'Customer contact information', 'bi-person-badge', 0);

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(4, 2, 'Deals', 'deals', 'Sales opportunities and pipeline', 'bi-currency-dollar', 1);

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(10, 2, 'Companies', 'companies', 'B2B client organizations', 'bi-building', 2);

INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(11, 2, 'Tasks', 'tasks', 'Sales follow-ups and actions', 'bi-list-check', 3);
