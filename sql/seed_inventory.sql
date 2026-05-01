-- ============================================================
-- Inventory System Sample App — Seeder SQL
-- Run AFTER schema.sql has been imported.
--
-- This seeder creates:
--   1. An admin user (admin@appforge.local / password123)
--   2. An "Inventory System" app
--   3. Products module with 6 fields
--   4. Categories module with 2 fields
--   5. Sample records for both modules
--   6. Dashboard widgets (count + bar chart)
--   7. Admin role with full permissions
--   8. Workflow: low-stock notification
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Admin User ───────────────────────────────────────────────
-- Password: password123 (bcrypt cost=12)
INSERT INTO users (id, name, email, password, is_admin) VALUES
(1, 'Admin User', 'admin@appforge.local',
 '$2y$12$OH7wli5rSqeTukZOQihIw.LKcI2NpeeFw9DI2OFj8aXM0EUQiNHv.', 1);

-- ── Inventory App ────────────────────────────────────────────
INSERT INTO apps (id, name, slug, description, icon, color, owner_id) VALUES
(1, 'Inventory System', 'inventory-system',
 'Track products, categories, stock levels, and pricing.', 'bi-box', '#6366f1', 1);

-- ── Admin Role ───────────────────────────────────────────────
INSERT INTO roles (id, app_id, name, description, is_system) VALUES
(1, 1, 'Admin', 'Full access to all modules', 1);

INSERT INTO user_roles (user_id, role_id, app_id) VALUES (1, 1, 1);

-- ── Categories Module ────────────────────────────────────────
INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(1, 1, 'Categories', 'categories', 'Product categories', 'bi-tag', 0);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, default_value, placeholder, help_text, validation, options, sort_order) VALUES
(1, 1, 'Category Name', 'category_name', 'text', 1, 1, 1, 1, NULL, 'e.g., Electronics', 'Unique category name', '{"min_length":2,"max_length":100}', NULL, 0),
(2, 1, 'Description', 'description', 'textarea', 0, 0, 1, 1, NULL, 'Describe this category...', NULL, NULL, NULL, 1);

-- ── Products Module ──────────────────────────────────────────
INSERT INTO modules (id, app_id, name, slug, description, icon, sort_order) VALUES
(2, 1, 'Products', 'products', 'Product inventory tracking', 'bi-box-seam', 1);

INSERT INTO fields (id, module_id, name, slug, field_type, is_required, is_unique, is_searchable, show_in_list, default_value, placeholder, help_text, validation, options, sort_order) VALUES
(3, 2, 'Product Name', 'product_name', 'text', 1, 0, 1, 1, NULL, 'e.g., Wireless Mouse', NULL, '{"min_length":2,"max_length":200}', NULL, 0),
(4, 2, 'SKU', 'sku', 'text', 1, 1, 1, 1, NULL, 'e.g., WM-001', 'Stock Keeping Unit — must be unique', '{"min_length":3,"max_length":30}', NULL, 1),
(5, 2, 'Category', 'category', 'dropdown', 1, 0, 1, 1, NULL, NULL, 'Select a product category', NULL, '{"choices":["Electronics","Office Supplies","Furniture","Food and Beverage","Clothing","Tools"]}', 2),
(6, 2, 'Price', 'price', 'number', 1, 0, 0, 1, '0', NULL, 'Unit price in USD', '{"min":0,"max":999999}', NULL, 3),
(7, 2, 'Stock Quantity', 'stock_quantity', 'number', 1, 0, 0, 1, '0', NULL, 'Current units in stock', '{"min":0}', NULL, 4),
(8, 2, 'Image', 'image', 'file', 0, 0, 0, 0, NULL, NULL, 'Product photo (JPG, PNG)', NULL, NULL, 5);

-- ── Admin Permissions for both modules ───────────────────────
INSERT INTO permissions (role_id, module_id, can_view, can_create, can_edit, can_delete) VALUES
(1, 1, 1, 1, 1, 1),
(1, 2, 1, 1, 1, 1);

-- ── Category Records ─────────────────────────────────────────
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(1, 1, 1, 1, '{"category_name":"Electronics","description":"Computers, phones, peripherals, and accessories."}'),
(2, 1, 1, 1, '{"category_name":"Office Supplies","description":"Pens, paper, folders, and desk items."}'),
(3, 1, 1, 1, '{"category_name":"Furniture","description":"Desks, chairs, shelving, and storage."}'),
(4, 1, 1, 1, '{"category_name":"Food and Beverage","description":"Snacks, drinks, and pantry supplies."}'),
(5, 1, 1, 1, '{"category_name":"Clothing","description":"Uniforms, workwear, and safety clothing."}'),
(6, 1, 1, 1, '{"category_name":"Tools","description":"Hand tools, power tools, and hardware."}');

INSERT INTO record_values (record_id, field_id, value) VALUES
(1, 1, 'Electronics'),    (1, 2, 'Computers, phones, peripherals, and accessories.'),
(2, 1, 'Office Supplies'),(2, 2, 'Pens, paper, folders, and desk items.'),
(3, 1, 'Furniture'),      (3, 2, 'Desks, chairs, shelving, and storage.'),
(4, 1, 'Food and Beverage'),(4, 2, 'Snacks, drinks, and pantry supplies.'),
(5, 1, 'Clothing'),       (5, 2, 'Uniforms, workwear, and safety clothing.'),
(6, 1, 'Tools'),          (6, 2, 'Hand tools, power tools, and hardware.');

-- ── Product Records ──────────────────────────────────────────
INSERT INTO records (id, app_id, module_id, created_by, data) VALUES
(7,  1, 2, 1, '{"product_name":"Wireless Mouse","sku":"WM-001","category":"Electronics","price":"29.99","stock_quantity":"150","image":""}'),
(8,  1, 2, 1, '{"product_name":"Mechanical Keyboard","sku":"MK-002","category":"Electronics","price":"89.99","stock_quantity":"75","image":""}'),
(9,  1, 2, 1, '{"product_name":"USB-C Hub","sku":"UH-003","category":"Electronics","price":"45.00","stock_quantity":"200","image":""}'),
(10, 1, 2, 1, '{"product_name":"Ballpoint Pen Pack (12)","sku":"BP-010","category":"Office Supplies","price":"8.50","stock_quantity":"500","image":""}'),
(11, 1, 2, 1, '{"product_name":"A4 Copy Paper (500 Sheets)","sku":"CP-011","category":"Office Supplies","price":"12.00","stock_quantity":"300","image":""}'),
(12, 1, 2, 1, '{"product_name":"Ergonomic Office Chair","sku":"EC-020","category":"Furniture","price":"349.00","stock_quantity":"25","image":""}'),
(13, 1, 2, 1, '{"product_name":"Standing Desk","sku":"SD-021","category":"Furniture","price":"599.00","stock_quantity":"12","image":""}'),
(14, 1, 2, 1, '{"product_name":"Instant Coffee (200g)","sku":"IC-030","category":"Food and Beverage","price":"6.99","stock_quantity":"400","image":""}'),
(15, 1, 2, 1, '{"product_name":"Safety Gloves (L)","sku":"SG-040","category":"Clothing","price":"15.00","stock_quantity":"3","image":""}'),
(16, 1, 2, 1, '{"product_name":"Cordless Drill","sku":"CD-050","category":"Tools","price":"129.00","stock_quantity":"8","image":""}'),
(17, 1, 2, 1, '{"product_name":"27-inch 4K Monitor","sku":"MN-004","category":"Electronics","price":"399.00","stock_quantity":"42","image":""}'),
(18, 1, 2, 1, '{"product_name":"Webcam HD 1080p","sku":"WC-005","category":"Electronics","price":"59.99","stock_quantity":"5","image":""}');

INSERT INTO record_values (record_id, field_id, value) VALUES
-- Wireless Mouse
(7,  3, 'Wireless Mouse'),        (7,  4, 'WM-001'),  (7,  5, 'Electronics'),    (7,  6, '29.99'),  (7,  7, '150'),
-- Mechanical Keyboard
(8,  3, 'Mechanical Keyboard'),    (8,  4, 'MK-002'),  (8,  5, 'Electronics'),    (8,  6, '89.99'),  (8,  7, '75'),
-- USB-C Hub
(9,  3, 'USB-C Hub'),              (9,  4, 'UH-003'),  (9,  5, 'Electronics'),    (9,  6, '45.00'),  (9,  7, '200'),
-- Ballpoint Pens
(10, 3, 'Ballpoint Pen Pack (12)'),(10, 4, 'BP-010'),  (10, 5, 'Office Supplies'),(10, 6, '8.50'),   (10, 7, '500'),
-- Copy Paper
(11, 3, 'A4 Copy Paper (500 Sheets)'),(11,4,'CP-011'), (11, 5, 'Office Supplies'),(11, 6, '12.00'),  (11, 7, '300'),
-- Office Chair
(12, 3, 'Ergonomic Office Chair'), (12, 4, 'EC-020'),  (12, 5, 'Furniture'),      (12, 6, '349.00'), (12, 7, '25'),
-- Standing Desk
(13, 3, 'Standing Desk'),          (13, 4, 'SD-021'),  (13, 5, 'Furniture'),      (13, 6, '599.00'), (13, 7, '12'),
-- Instant Coffee
(14, 3, 'Instant Coffee (200g)'),  (14, 4, 'IC-030'),  (14, 5, 'Food and Beverage'),(14, 6, '6.99'),  (14, 7, '400'),
-- Safety Gloves (LOW STOCK)
(15, 3, 'Safety Gloves (L)'),      (15, 4, 'SG-040'),  (15, 5, 'Clothing'),       (15, 6, '15.00'), (15, 7, '3'),
-- Cordless Drill
(16, 3, 'Cordless Drill'),         (16, 4, 'CD-050'),  (16, 5, 'Tools'),          (16, 6, '129.00'),(16, 7, '8'),
-- 4K Monitor
(17, 3, '27-inch 4K Monitor'),     (17, 4, 'MN-004'),  (17, 5, 'Electronics'),    (17, 6, '399.00'),(17, 7, '42'),
-- Webcam (LOW STOCK)
(18, 3, 'Webcam HD 1080p'),        (18, 4, 'WC-005'),  (18, 5, 'Electronics'),    (18, 6, '59.99'), (18, 7, '5');

-- ── Dashboard Widgets ────────────────────────────────────────

-- Widget 1: Total Product Count
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(1, 1, 1, 'Total Products', 'count', 2, NULL, '#6366f1', 3);

-- Widget 2: Total Categories
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(2, 1, 1, 'Total Categories', 'count', 1, NULL, '#22c55e', 3);

-- Widget 3: Total Inventory Value (sum of price * quantity approximated as sum of price)
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(3, 1, 1, 'Avg Product Price', 'average', 2, 6, '#f97316', 3);

-- Widget 4: Low Stock Alert Count (stock < 10)
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, filters, chart_color, width) VALUES
(4, 1, 1, 'Low Stock Items', 'count', 2, NULL, '[{"field_id":7,"operator":"lt","value":"10"}]', '#f43f5e', 3);

-- Widget 5: Products by Category (bar chart)
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(5, 1, 1, 'Products by Category', 'bar_chart', 2, 5, '#8b5cf6', 6);

-- Widget 6: Category Distribution (pie chart)
INSERT INTO dashboard_widgets (id, app_id, user_id, title, widget_type, module_id, field_id, chart_color, width) VALUES
(6, 1, 1, 'Category Distribution', 'pie_chart', 2, 5, '#14b8a6', 6);

-- ── Low Stock Workflow ───────────────────────────────────────
-- Trigger: when a product is updated and stock_quantity < 10, notify admins
INSERT INTO workflows (id, module_id, name, trigger_on, is_active, conditions, condition_logic) VALUES
(1, 2, 'Low Stock Alert', 'create_update', 1,
 '[{"field_slug":"stock_quantity","operator":"lt","value":"10"}]', 'AND');

INSERT INTO workflow_actions (id, workflow_id, action_type, sort_order, config) VALUES
(1, 1, 'notification', 0,
 '{"message":"⚠️ Low stock alert: {{product_name}} (SKU: {{sku}}) has only {{stock_quantity}} units remaining."}');

SET FOREIGN_KEY_CHECKS = 1;
