<?php
// Payroll Management System — Template Data
return [
    'modules' => [
        ['id'=>1, 'name'=>'Departments', 'slug'=>'departments', 'description'=>'Company organizational units', 'icon'=>'bi-diagram-3', 'sort_order'=>0],
        ['id'=>2, 'name'=>'Employees',   'slug'=>'employees',   'description'=>'Staff directory & payroll settings', 'icon'=>'bi-people', 'sort_order'=>1],
        ['id'=>3, 'name'=>'Attendance',  'slug'=>'attendance',  'description'=>'Daily time records (DTR)', 'icon'=>'bi-clock-history', 'sort_order'=>2],
        ['id'=>4, 'name'=>'Payroll Runs','slug'=>'payroll_runs','description'=>'Grouped salary computations', 'icon'=>'bi-cash-stack', 'sort_order'=>3],
        ['id'=>5, 'name'=>'Payslips',    'slug'=>'payslips',    'description'=>'Individual employee earnings', 'icon'=>'bi-file-earmark-text', 'sort_order'=>4],
    ],
    'fields' => [
        // Departments
        ['id'=>1, 'module_id'=>1, 'name'=>'Dept Name', 'slug'=>'name', 'type'=>'text', 'required'=>1, 'unique'=>1, 'searchable'=>1, 'in_list'=>1],
        
        // Employees
        ['id'=>10, 'module_id'=>2, 'name'=>'Employee ID', 'slug'=>'emp_id', 'type'=>'text', 'required'=>1, 'unique'=>1, 'searchable'=>1, 'in_list'=>1],
        ['id'=>11, 'module_id'=>2, 'name'=>'Full Name', 'slug'=>'name', 'type'=>'text', 'required'=>1, 'unique'=>0, 'searchable'=>1, 'in_list'=>1],
        ['id'=>12, 'module_id'=>2, 'name'=>'Dept', 'slug'=>'dept', 'type'=>'lookup', 'required'=>1, 'options'=>['target_module_id'=>1, 'display_field_slug'=>'name'], 'in_list'=>1],
        ['id'=>13, 'module_id'=>2, 'name'=>'Employment Type', 'slug'=>'type', 'type'=>'dropdown', 'options'=>['choices'=>['Full-time','Part-time','Contract']], 'in_list'=>1],
        ['id'=>14, 'module_id'=>2, 'name'=>'Salary Type', 'slug'=>'salary_type', 'type'=>'dropdown', 'options'=>['choices'=>['Monthly','Daily','Hourly']], 'in_list'=>0],
        ['id'=>15, 'module_id'=>2, 'name'=>'Base Salary', 'slug'=>'base_salary', 'type'=>'number', 'required'=>1, 'in_list'=>1],
        ['id'=>16, 'module_id'=>2, 'name'=>'Bank Details', 'slug'=>'bank', 'type'=>'textarea', 'in_list'=>0],
        ['id'=>17, 'module_id'=>2, 'name'=>'Contract', 'slug'=>'contract', 'type'=>'file', 'in_list'=>0],
        
        // Attendance
        ['id'=>30, 'module_id'=>3, 'name'=>'Employee', 'slug'=>'emp', 'type'=>'lookup', 'required'=>1, 'options'=>['target_module_id'=>2, 'display_field_slug'=>'name'], 'in_list'=>1],
        ['id'=>31, 'module_id'=>3, 'name'=>'Date', 'slug'=>'date', 'type'=>'date', 'required'=>1, 'in_list'=>1],
        ['id'=>32, 'module_id'=>3, 'name'=>'Time In', 'slug'=>'time_in', 'type'=>'text', 'placeholder'=>'08:00', 'in_list'=>1],
        ['id'=>33, 'module_id'=>3, 'name'=>'Time Out', 'slug'=>'time_out', 'type'=>'text', 'placeholder'=>'17:00', 'in_list'=>1],
        ['id'=>34, 'module_id'=>3, 'name'=>'OT Hours', 'slug'=>'ot', 'type'=>'number', 'default_value'=>'0', 'in_list'=>1],
        
        // Payroll Runs
        ['id'=>40, 'module_id'=>4, 'name'=>'Period Name', 'slug'=>'name', 'type'=>'text', 'required'=>1, 'placeholder'=>'May 2026 - 1st Cutoff', 'in_list'=>1],
        ['id'=>41, 'module_id'=>4, 'name'=>'Start Date', 'slug'=>'start', 'type'=>'date', 'required'=>1, 'in_list'=>1],
        ['id'=>42, 'module_id'=>4, 'name'=>'End Date', 'slug'=>'end', 'type'=>'date', 'required'=>1, 'in_list'=>1],
        ['id'=>43, 'module_id'=>4, 'name'=>'Status', 'slug'=>'status', 'type'=>'dropdown', 'options'=>['choices'=>['Draft','Processing','Approved','Paid']], 'in_list'=>1],
        
        // Payslips
        ['id'=>50, 'module_id'=>5, 'name'=>'Employee', 'slug'=>'emp', 'type'=>'lookup', 'required'=>1, 'options'=>['target_module_id'=>2, 'display_field_slug'=>'name'], 'in_list'=>1],
        ['id'=>51, 'module_id'=>5, 'name'=>'Payroll Run', 'slug'=>'run', 'type'=>'lookup', 'required'=>1, 'options'=>['target_module_id'=>4, 'display_field_slug'=>'name'], 'in_list'=>1],
        ['id'=>52, 'module_id'=>5, 'name'=>'Basic Pay', 'slug'=>'basic', 'type'=>'number', 'in_list'=>1],
        ['id'=>53, 'module_id'=>5, 'name'=>'Deductions', 'slug'=>'deductions', 'type'=>'number', 'in_list'=>1],
        ['id'=>54, 'module_id'=>5, 'name'=>'Net Pay', 'slug'=>'net', 'type'=>'number', 'in_list'=>1],
        ['id'=>55, 'module_id'=>5, 'name'=>'PDF Payslip', 'slug'=>'pdf', 'type'=>'file', 'in_list'=>1],
    ],
    'records' => [
        ['module_id'=>1, 'data'=>['name'=>'Engineering']],
        ['module_id'=>1, 'data'=>['name'=>'Sales']],
        ['module_id'=>1, 'data'=>['name'=>'HR & Admin']],
        
        ['module_id'=>2, 'data'=>['emp_id'=>'EMP-001', 'name'=>'John Doe', 'dept'=>'1', 'type'=>'Full-time', 'salary_type'=>'Monthly', 'base_salary'=>'50000']],
        ['module_id'=>2, 'data'=>['emp_id'=>'EMP-002', 'name'=>'Jane Smith', 'dept'=>'1', 'type'=>'Full-time', 'salary_type'=>'Monthly', 'base_salary'=>'65000']],
        ['module_id'=>2, 'data'=>['emp_id'=>'EMP-003', 'name'=>'Bob Wilson', 'dept'=>'2', 'type'=>'Full-time', 'salary_type'=>'Monthly', 'base_salary'=>'45000']],
        
        ['module_id'=>4, 'data'=>['name'=>'May 2026 - 1st Half', 'start'=>'2026-05-01', 'end'=>'2026-05-15', 'status'=>'Draft']],
    ],
    'widgets' => [
        ['title'=>'Total Employees', 'type'=>'count', 'module_id'=>2, 'color'=>'#6366f1', 'width'=>3],
        ['title'=>'Current Payroll Cost', 'type'=>'sum', 'module_id'=>2, 'field_id'=>15, 'color'=>'#10b981', 'width'=>3],
        ['title'=>'Pending Leave Reqs', 'type'=>'count', 'module_id'=>3, 'color'=>'#f59e0b', 'width'=>3],
        ['title'=>'Dept Distribution', 'type'=>'pie_chart', 'module_id'=>2, 'field_id'=>12, 'color'=>'#8b5cf6', 'width'=>6],
        ['title'=>'Salary Range', 'type'=>'bar_chart', 'module_id'=>2, 'field_id'=>15, 'color'=>'#3b82f6', 'width'=>6],
    ],
];
