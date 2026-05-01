# Modulyn — No-Code Web Application Builder

**Modulyn** is a highly scalable, robust, and intuitive no-code platform that allows users to build fully customized web applications entirely from a visual interface—without writing a single line of code.

Built entirely in native PHP 8 and MySQL, it utilizes an advanced Hybrid EAV (Entity-Attribute-Value) architecture to dynamically generate database schemas, routing, REST APIs, and UI components on the fly. 

**Developed by Bryan James Dionisio**

---

## 🚀 Features

* **Visual Module Builder**: A 3-panel drag-and-drop workspace that lets you design data structures. Add fields (Text, Number, Dropdowns, Files, Checkboxes) and immediately preview the generated forms.
* **Hybrid EAV Data Engine**: Highly optimized data storage utilizing JSON snapshots. This eliminates the "N+1 query" problem typical in dynamic databases and allows for lightning-fast list views and REST APIs.
* **Dynamic Dashboards**: Create real-time analytics widgets using built-in SQL aggregations (Count, Sum, Average) and visualize them via Chart.js integration.
* **Automated REST APIs**: The moment you create a module, a fully functional, authenticated REST API is instantly generated for it.
* **Workflow Automation**: Set up conditional "If/Then" triggers. Automatically send emails, update fields, or push in-app notifications when records are created, updated, or deleted.
* **Role-Based Access Control (RBAC)**: Fine-grained permissions allowing you to control which users can view, create, edit, or delete records on a per-module basis.
* **Plugin Architecture**: A fully extensible event-driven plugin manager. Developers can hook into system lifecycles without ever touching core files (includes a sample `AuditLog` plugin).
* **Premium Dark Mode UI**: A stunning, modern interface featuring glassmorphism elements, custom Bootstrap 5 variables, and fluid CSS micro-animations.

---

## 🛠️ Technology Stack

* **Backend**: PHP 8.x (Native MVC framework, zero heavy dependencies)
* **Database**: MySQL 8+ / MariaDB (PDO Prepared Statements)
* **Frontend**: HTML5, Vanilla JavaScript, CSS3
* **Styling**: Bootstrap 5, Bootstrap Icons
* **Charts**: Chart.js 4.4

---

## ⚙️ Installation & Setup

1. **Clone the Repository**
   Place the project inside your local web server environment (e.g., Laragon, XAMPP, or a dedicated VPS).
   ```bash
   git clone https://github.com/yourusername/modulyn.git
   cd modulyn
   ```

2. **Database Setup**
   * Create a new MySQL database named `no_code_app`.
   * Import the schema and initial data seeds:
     ```bash
     mysql -u root -p no_code_app < sql/schema.sql
     mysql -u root -p no_code_app < sql/seed_inventory.sql
     ```

3. **Configuration**
   * Open `config/database.php` and update the connection credentials if necessary:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_PORT', '3306');
     define('DB_NAME', 'no_code_app');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```
   * The `config/app.php` auto-detects your environment and base URLs dynamically, so no strict URL configuration is needed.

4. **Security**
   * Ensure that `.htaccess` is processed by your web server (Apache) so that all requests are securely routed through `index.php`.
   * The `storage/uploads` folder must be writable by the web server.

---

## 📖 Usage

### Getting Started
Log in to the system using the default administrative account (seeded from `seed_inventory.sql`):
* **Email**: `admin@modulyn.local`
* **Password**: `password123`

### Creating Your First App
1. Navigate to "My Apps" and click "New Application".
2. Open the Builder and create a new **Module** (e.g., "Customers").
3. Drag and drop fields into the module.
4. Your application is now live! Navigate to the generated "Customers" tab on your sidebar to start inserting records.

---

## 🔒 Architecture Note

Modulyn relies heavily on the Front Controller pattern. Every request is routed through `index.php` and dispatched by the internal `Core\Router`. Security boundaries, such as CSRF protection and role-based authentication checks, are strictly enforced at the middleware and engine levels.

## 📄 License
This project is proprietary and built by **Bryan James Dionisio**. All rights reserved.
