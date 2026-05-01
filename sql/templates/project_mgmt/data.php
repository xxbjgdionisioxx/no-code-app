<?php
// Project Tracker — Template Data
return [
    'modules' => [
        ['id' => 1, 'name' => 'Projects',   'slug' => 'projects',   'description' => 'High-level project tracking',      'icon' => 'bi-folder',        'sort_order' => 0],
        ['id' => 2, 'name' => 'Tasks',      'slug' => 'tasks',      'description' => 'Individual project tasks',          'icon' => 'bi-check2-square', 'sort_order' => 1],
        ['id' => 3, 'name' => 'Milestones', 'slug' => 'milestones', 'description' => 'Key project markers and deadlines', 'icon' => 'bi-flag',          'sort_order' => 2],
        ['id' => 4, 'name' => 'Time Logs',  'slug' => 'time_logs',  'description' => 'Hours logged per task',             'icon' => 'bi-clock-history', 'sort_order' => 3],
    ],
    'fields' => [
        // Projects
        ['id' => 1, 'module_id' => 1, 'name' => 'Project Name', 'slug' => 'name',       'type' => 'text',     'required' => 1, 'unique' => 1, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 2, 'module_id' => 1, 'name' => 'Status',       'slug' => 'status',     'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 1, 'options' => ['choices' => ['Planning','Active','On Hold','Completed']]],
        ['id' => 3, 'module_id' => 1, 'name' => 'Budget',       'slug' => 'budget',     'type' => 'number',   'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 2],
        ['id' => 4, 'module_id' => 1, 'name' => 'Start Date',   'slug' => 'start_date', 'type' => 'date',     'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 3],
        ['id' => 5, 'module_id' => 1, 'name' => 'Due Date',     'slug' => 'due_date',   'type' => 'date',     'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 4],
        // Tasks
        ['id' => 6, 'module_id' => 2, 'name' => 'Task Name', 'slug' => 'name',     'type' => 'text',     'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 7, 'module_id' => 2, 'name' => 'Priority',  'slug' => 'priority', 'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 1, 'options' => ['choices' => ['Low','Normal','High','Critical']]],
        ['id' => 8, 'module_id' => 2, 'name' => 'Status',    'slug' => 'status',   'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 2, 'options' => ['choices' => ['Backlog','In Progress','Review','Done']]],
        ['id' => 9, 'module_id' => 2, 'name' => 'Due Date',  'slug' => 'due_date', 'type' => 'date',     'required' => 0, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 3],
        ['id' => 10, 'module_id' => 2, 'name' => 'Assignee',  'slug' => 'assignee', 'type' => 'text',     'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 4],
        // Milestones
        ['id' => 11, 'module_id' => 3, 'name' => 'Milestone', 'slug' => 'name',   'type' => 'text',     'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 12, 'module_id' => 3, 'name' => 'Date',      'slug' => 'date',   'type' => 'date',     'required' => 1, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 1],
        ['id' => 13, 'module_id' => 3, 'name' => 'Status',    'slug' => 'status', 'type' => 'dropdown', 'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 2, 'options' => ['choices' => ['Upcoming','Reached','Missed']]],
        // Time Logs
        ['id' => 14, 'module_id' => 4, 'name' => 'Task',      'slug' => 'task',  'type' => 'text',   'required' => 1, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 0],
        ['id' => 15, 'module_id' => 4, 'name' => 'Hours',     'slug' => 'hours', 'type' => 'number', 'required' => 1, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 1],
        ['id' => 16, 'module_id' => 4, 'name' => 'Log Date',  'slug' => 'date',  'type' => 'date',   'required' => 1, 'unique' => 0, 'searchable' => 0, 'in_list' => 1, 'sort' => 2],
        ['id' => 17, 'module_id' => 4, 'name' => 'Developer', 'slug' => 'dev',   'type' => 'text',   'required' => 0, 'unique' => 0, 'searchable' => 1, 'in_list' => 1, 'sort' => 3],
    ],
    'records' => [
        // Projects
        ['module_id' => 1, 'data' => ['name' => 'Website Redesign', 'status' => 'Active',     'budget' => '15000', 'start_date' => '2025-04-01', 'due_date' => '2025-07-31']],
        ['module_id' => 1, 'data' => ['name' => 'Mobile App Q4',    'status' => 'Planning',   'budget' => '40000', 'start_date' => '2025-07-01', 'due_date' => '2025-12-31']],
        ['module_id' => 1, 'data' => ['name' => 'Cloud Migration',  'status' => 'Completed',  'budget' => '25000', 'start_date' => '2025-01-01', 'due_date' => '2025-04-30']],
        ['module_id' => 1, 'data' => ['name' => 'Data Warehouse',   'status' => 'On Hold',    'budget' => '30000', 'start_date' => '2025-05-01', 'due_date' => '2025-09-30']],
        // Tasks
        ['module_id' => 2, 'data' => ['name' => 'Design Homepage',  'priority' => 'High',     'status' => 'In Progress', 'due_date' => '2025-06-15', 'assignee' => 'Alice']],
        ['module_id' => 2, 'data' => ['name' => 'API Integration',  'priority' => 'Critical', 'status' => 'Backlog',     'due_date' => '2025-06-20', 'assignee' => 'Bob']],
        ['module_id' => 2, 'data' => ['name' => 'User Testing',     'priority' => 'Normal',   'status' => 'Review',      'due_date' => '2025-07-01', 'assignee' => 'Carol']],
        ['module_id' => 2, 'data' => ['name' => 'Setup CI/CD',      'priority' => 'High',     'status' => 'Done',        'due_date' => '2025-05-30', 'assignee' => 'Bob']],
        ['module_id' => 2, 'data' => ['name' => 'Write Docs',       'priority' => 'Low',      'status' => 'Backlog',     'due_date' => '2025-07-15', 'assignee' => 'Alice']],
        // Milestones
        ['module_id' => 3, 'data' => ['name' => 'Design Approved',    'date' => '2025-05-15', 'status' => 'Reached']],
        ['module_id' => 3, 'data' => ['name' => 'Beta Launch',        'date' => '2025-06-30', 'status' => 'Upcoming']],
        ['module_id' => 3, 'data' => ['name' => 'Production Go-Live', 'date' => '2025-07-31', 'status' => 'Upcoming']],
        // Time Logs
        ['module_id' => 4, 'data' => ['task' => 'Homepage Design', 'hours' => '4', 'date' => '2025-06-02', 'dev' => 'Alice']],
        ['module_id' => 4, 'data' => ['task' => 'API Integration', 'hours' => '6', 'date' => '2025-06-03', 'dev' => 'Bob']],
        ['module_id' => 4, 'data' => ['task' => 'Unit Tests',      'hours' => '3', 'date' => '2025-06-04', 'dev' => 'Carol']],
        ['module_id' => 4, 'data' => ['task' => 'CI/CD Setup',     'hours' => '8', 'date' => '2025-05-30', 'dev' => 'Bob']],
    ],
    'widgets' => [
        ['title' => 'Active Projects', 'type' => 'count',     'module_id' => 1, 'color' => '#3b82f6', 'width' => 3],
        ['title' => 'Total Tasks',     'type' => 'count',     'module_id' => 2, 'color' => '#10b981', 'width' => 3],
        ['title' => 'Billable Hours',  'type' => 'sum',       'module_id' => 4, 'field_id' => 15, 'color' => '#f59e0b', 'width' => 3],
        ['title' => 'Milestones Met',  'type' => 'count',     'module_id' => 3, 'color' => '#8b5cf6', 'width' => 3],
        ['title' => 'Task Priorities', 'type' => 'pie_chart', 'module_id' => 2, 'field_id' => 7, 'color' => '#ec4899', 'width' => 6],
        ['title' => 'Project Status',  'type' => 'bar_chart', 'module_id' => 1, 'field_id' => 2, 'color' => '#3b82f6', 'width' => 6],
    ],
];
