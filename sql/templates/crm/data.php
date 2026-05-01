<?php
// Sales CRM — Template Data
// Used by TemplateEngine::installTemplate()
return [
    'modules' => [
        ['id' => 1, 'name' => 'Contacts',  'slug' => 'contacts',  'description' => 'Customer contact information',    'icon' => 'bi-person-badge',    'sort_order' => 0],
        ['id' => 2, 'name' => 'Deals',     'slug' => 'deals',     'description' => 'Sales opportunities & pipeline', 'icon' => 'bi-currency-dollar', 'sort_order' => 1],
        ['id' => 3, 'name' => 'Companies', 'slug' => 'companies', 'description' => 'B2B client organizations',       'icon' => 'bi-building',        'sort_order' => 2],
        ['id' => 4, 'name' => 'Tasks',     'slug' => 'tasks',     'description' => 'Sales follow-ups and actions',   'icon' => 'bi-list-check',      'sort_order' => 3],
    ],
    'fields' => [
        // Contacts
        ['id' => 1, 'module_id' => 1, 'name' => 'Full Name', 'slug' => 'full_name', 'type' => 'text',     'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 2, 'module_id' => 1, 'name' => 'Email',     'slug' => 'email',     'type' => 'text',     'required' => 1, 'unique' => 1, 'searchable' => 1, 'in_list' => 1, 'sort' => 1],
        ['id' => 3, 'module_id' => 1, 'name' => 'Phone',     'slug' => 'phone',     'type' => 'text',     'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 2],
        ['id' => 4, 'module_id' => 1, 'name' => 'Status',    'slug' => 'status',    'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 3, 'options' => ['choices' => ['New Lead','Contacted','Qualified','Customer','Lost']]],
        ['id' => 5, 'module_id' => 1, 'name' => 'Notes',     'slug' => 'notes',     'type' => 'textarea', 'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 0, 'sort' => 4],
        // Deals
        ['id' => 6, 'module_id' => 2, 'name' => 'Deal Name',    'slug' => 'deal_name',    'type' => 'text',     'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 7, 'module_id' => 2, 'name' => 'Amount',       'slug' => 'amount',       'type' => 'number',   'required' => 1, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 1],
        ['id' => 8, 'module_id' => 2, 'name' => 'Stage',        'slug' => 'stage',        'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 2, 'options' => ['choices' => ['Discovery','Proposal','Negotiation','Closed Won','Closed Lost']]],
        ['id' => 9, 'module_id' => 2, 'name' => 'Closing Date', 'slug' => 'closing_date', 'type' => 'date',     'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 3],
        ['id' => 10, 'module_id' => 2, 'name' => 'Contact Name', 'slug' => 'contact',      'type' => 'text',     'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 4],
        // Companies
        ['id' => 11, 'module_id' => 3, 'name' => 'Company Name', 'slug' => 'name',     'type' => 'text',     'required' => 1, 'unique' => 1, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 12, 'module_id' => 3, 'name' => 'Industry',     'slug' => 'industry', 'type' => 'dropdown', 'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 1, 'options' => ['choices' => ['Technology','Finance','Healthcare','Manufacturing','Retail','Other']]],
        ['id' => 13, 'module_id' => 3, 'name' => 'Website',      'slug' => 'website',  'type' => 'text',     'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 2],
        ['id' => 14, 'module_id' => 3, 'name' => 'Employees',    'slug' => 'size',     'type' => 'dropdown', 'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 3, 'options' => ['choices' => ['1-10','11-50','51-200','201-1000','1000+']]],
        // Tasks
        ['id' => 15, 'module_id' => 4, 'name' => 'Subject',     'slug' => 'subject',     'type' => 'text',     'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 16, 'module_id' => 4, 'name' => 'Due Date',    'slug' => 'due_date',    'type' => 'date',     'required' => 1, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 1],
        ['id' => 17, 'module_id' => 4, 'name' => 'Priority',    'slug' => 'priority',    'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 2, 'options' => ['choices' => ['Low','Medium','High']]],
        ['id' => 18, 'module_id' => 4, 'name' => 'Status',      'slug' => 'status',      'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 3, 'options' => ['choices' => ['To Do','In Progress','Done']]],
        ['id' => 19, 'module_id' => 4, 'name' => 'Assigned To', 'slug' => 'assigned_to', 'type' => 'text',     'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 4],
    ],
    'records' => [
        // Companies
        ['module_id' => 3, 'data' => ['name' => 'Acme Corp',       'industry' => 'Technology',    'website' => 'acmecorp.com',  'size' => '201-1000']],
        ['module_id' => 3, 'data' => ['name' => 'Globex Inc',       'industry' => 'Finance',       'website' => 'globex.com',    'size' => '51-200']],
        ['module_id' => 3, 'data' => ['name' => 'Stark Industries',  'industry' => 'Manufacturing', 'website' => 'stark.com',     'size' => '1000+']],
        ['module_id' => 3, 'data' => ['name' => 'Health Co',         'industry' => 'Healthcare',    'website' => 'healthco.com',  'size' => '11-50']],
        // Contacts
        ['module_id' => 1, 'data' => ['full_name' => 'John Doe',    'email' => 'john@acmecorp.com',  'phone' => '+1 555-0101', 'status' => 'Customer',   'notes' => 'Longtime client, VIP tier.']],
        ['module_id' => 1, 'data' => ['full_name' => 'Jane Smith',  'email' => 'jane@globex.com',    'phone' => '+1 555-0202', 'status' => 'Qualified',  'notes' => 'Interested in enterprise plan.']],
        ['module_id' => 1, 'data' => ['full_name' => 'Tony Stark',  'email' => 'tony@stark.com',     'phone' => '+1 555-0303', 'status' => 'New Lead',   'notes' => 'Met at TechConf 2025.']],
        ['module_id' => 1, 'data' => ['full_name' => 'Maria Garcia','email' => 'maria@healthco.com', 'phone' => '+1 555-0404', 'status' => 'Contacted',  'notes' => 'Sent demo video.']],
        ['module_id' => 1, 'data' => ['full_name' => 'Dave Lee',    'email' => 'dave@retailmax.com', 'phone' => '+1 555-0505', 'status' => 'Lost',       'notes' => 'Chose competitor.']],
        // Deals
        ['module_id' => 2, 'data' => ['deal_name' => 'Server Expansion',    'amount' => '50000', 'stage' => 'Proposal',    'closing_date' => '2025-07-01', 'contact' => 'John Doe']],
        ['module_id' => 2, 'data' => ['deal_name' => 'Software Licenses',   'amount' => '12000', 'stage' => 'Negotiation', 'closing_date' => '2025-06-15', 'contact' => 'Jane Smith']],
        ['module_id' => 2, 'data' => ['deal_name' => 'Consulting Retainer', 'amount' => '8500',  'stage' => 'Closed Won',  'closing_date' => '2025-05-10', 'contact' => 'Maria Garcia']],
        ['module_id' => 2, 'data' => ['deal_name' => 'Annual SaaS Plan',    'amount' => '36000', 'stage' => 'Discovery',   'closing_date' => '2025-08-01', 'contact' => 'Tony Stark']],
        ['module_id' => 2, 'data' => ['deal_name' => 'Hardware Refresh',    'amount' => '22000', 'stage' => 'Closed Lost', 'closing_date' => '2025-04-20', 'contact' => 'Dave Lee']],
        // Tasks
        ['module_id' => 4, 'data' => ['subject' => 'Send demo to Jane',     'due_date' => '2025-06-05', 'priority' => 'High',   'status' => 'To Do',       'assigned_to' => 'Sales Team']],
        ['module_id' => 4, 'data' => ['subject' => 'Follow up with Acme',   'due_date' => '2025-06-10', 'priority' => 'Medium', 'status' => 'In Progress', 'assigned_to' => 'John']],
        ['module_id' => 4, 'data' => ['subject' => 'Prepare proposal doc',  'due_date' => '2025-06-12', 'priority' => 'High',   'status' => 'To Do',       'assigned_to' => 'Maria']],
        ['module_id' => 4, 'data' => ['subject' => 'Close Stark deal',      'due_date' => '2025-07-01', 'priority' => 'High',   'status' => 'In Progress', 'assigned_to' => 'Sales Team']],
    ],
    'widgets' => [
        ['title' => 'Total Contacts',   'type' => 'count',     'module_id' => 1, 'color' => '#f59e0b', 'width' => 3],
        ['title' => 'Pipeline Value',   'type' => 'sum',       'module_id' => 2, 'field_id' => 7, 'color' => '#10b981', 'width' => 3],
        ['title' => 'Active Companies', 'type' => 'count',     'module_id' => 3, 'color' => '#6366f1', 'width' => 3],
        ['title' => 'Open Tasks',       'type' => 'count',     'module_id' => 4, 'color' => '#3b82f6', 'width' => 3],
        ['title' => 'Deals by Stage',   'type' => 'bar_chart', 'module_id' => 2, 'field_id' => 8, 'color' => '#f59e0b', 'width' => 6],
        ['title' => 'Lead Status Mix',  'type' => 'pie_chart', 'module_id' => 1, 'field_id' => 4, 'color' => '#ec4899', 'width' => 6],
    ],
];
