<?php
// HR Portal — Template Data
return [
    'modules' => [
        ['id'=>1,'name'=>'Employees',  'slug'=>'employees',  'description'=>'Staff directory',           'icon'=>'bi-people',        'sort_order'=>0],
        ['id'=>2,'name'=>'Departments','slug'=>'departments','description'=>'Company organization units', 'icon'=>'bi-diagram-3',     'sort_order'=>1],
        ['id'=>3,'name'=>'Leaves',     'slug'=>'leaves',     'description'=>'Time-off requests',         'icon'=>'bi-calendar-event','sort_order'=>2],
    ],
    'fields' => [
        ['id'=>1, 'module_id'=>1,'name'=>'Full Name', 'slug'=>'name',     'type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>2, 'module_id'=>1,'name'=>'Department','slug'=>'dept',     'type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>1,'options'=>['choices'=>['IT','Sales','Marketing','HR','Finance','Operations']]],
        ['id'=>3, 'module_id'=>1,'name'=>'Job Title', 'slug'=>'job_title','type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>2],
        ['id'=>4, 'module_id'=>1,'name'=>'Salary',    'slug'=>'salary',   'type'=>'number',  'required'=>0,'unique'=>0,'searchable'=>0,'in_list'=>0,'sort'=>3],
        ['id'=>5, 'module_id'=>1,'name'=>'Hire Date', 'slug'=>'hire_date','type'=>'date',    'required'=>1,'unique'=>0,'searchable'=>0,'in_list'=>1,'sort'=>4],
        ['id'=>6, 'module_id'=>2,'name'=>'Dept Name', 'slug'=>'name',     'type'=>'text',    'required'=>1,'unique'=>1,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>7, 'module_id'=>2,'name'=>'Manager',   'slug'=>'manager',  'type'=>'text',    'required'=>0,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>1],
        ['id'=>8, 'module_id'=>2,'name'=>'Budget',    'slug'=>'budget',   'type'=>'number',  'required'=>0,'unique'=>0,'searchable'=>0,'in_list'=>1,'sort'=>2],
        ['id'=>9, 'module_id'=>3,'name'=>'Employee',  'slug'=>'employee',  'type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>10, 'module_id'=>3,'name'=>'Leave Type','slug'=>'type',      'type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>1,'options'=>['choices'=>['Sick','Vacation','Personal','Bereavement','Parental']]],
        ['id'=>11, 'module_id'=>3,'name'=>'Start Date','slug'=>'start_date','type'=>'date',    'required'=>1,'unique'=>0,'searchable'=>0,'in_list'=>1,'sort'=>2],
        ['id'=>12, 'module_id'=>3,'name'=>'Days',      'slug'=>'days',      'type'=>'number',  'required'=>1,'unique'=>0,'searchable'=>0,'in_list'=>1,'sort'=>3],
        ['id'=>13, 'module_id'=>3,'name'=>'Status',    'slug'=>'status',    'type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>4,'options'=>['choices'=>['Pending','Approved','Rejected']]],
    ],
    'records' => [
        ['module_id'=>2,'data'=>['name'=>'Information Technology','manager'=>'Alice Johnson','budget'=>'500000']],
        ['module_id'=>2,'data'=>['name'=>'Global Sales','manager'=>'Bob Miller','budget'=>'350000']],
        ['module_id'=>2,'data'=>['name'=>'Human Resources','manager'=>'Carol White','budget'=>'200000']],
        ['module_id'=>1,'data'=>['name'=>'Alice Johnson','dept'=>'IT','job_title'=>'Senior Developer','salary'=>'85000','hire_date'=>'2022-03-15']],
        ['module_id'=>1,'data'=>['name'=>'Bob Miller','dept'=>'Sales','job_title'=>'Sales Executive','salary'=>'62000','hire_date'=>'2021-07-01']],
        ['module_id'=>1,'data'=>['name'=>'Carol White','dept'=>'HR','job_title'=>'HR Manager','salary'=>'71000','hire_date'=>'2020-01-10']],
        ['module_id'=>1,'data'=>['name'=>'Dave Brown','dept'=>'Finance','job_title'=>'Accountant','salary'=>'68000','hire_date'=>'2023-09-05']],
        ['module_id'=>3,'data'=>['employee'=>'Alice Johnson','type'=>'Vacation','start_date'=>'2025-07-01','days'=>'10','status'=>'Approved']],
        ['module_id'=>3,'data'=>['employee'=>'Bob Miller','type'=>'Sick','start_date'=>'2025-06-10','days'=>'3','status'=>'Approved']],
        ['module_id'=>3,'data'=>['employee'=>'Eve Davis','type'=>'Personal','start_date'=>'2025-06-20','days'=>'2','status'=>'Pending']],
    ],
    'widgets' => [
        ['title'=>'Total Headcount',  'type'=>'count',    'module_id'=>1,'color'=>'#ec4899','width'=>3],
        ['title'=>'Departments',      'type'=>'count',    'module_id'=>2,'color'=>'#3b82f6','width'=>3],
        ['title'=>'Open Leave Reqs',  'type'=>'count',    'module_id'=>3,'color'=>'#f59e0b','width'=>3],
        ['title'=>'Monthly Payroll',  'type'=>'sum',      'module_id'=>1, 'field_id'=>4, 'color'=>'#10b981','width'=>3],
        ['title'=>'Dept Distribution','type'=>'pie_chart','module_id'=>1, 'field_id'=>2, 'color'=>'#ec4899','width'=>6],
        ['title'=>'Leave by Type',    'type'=>'bar_chart','module_id'=>3, 'field_id'=>10, 'color'=>'#f43f5e','width'=>6],
    ],
];
