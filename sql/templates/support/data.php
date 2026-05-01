<?php
// Help Desk — Template Data
return [
    'modules' => [
        ['id'=>1,'name'=>'Tickets',       'slug'=>'tickets', 'description'=>'Support requests',       'icon'=>'bi-ticket-perforated','sort_order'=>0],
        ['id'=>2,'name'=>'Knowledge Base','slug'=>'kb',      'description'=>'Internal help articles', 'icon'=>'bi-book',             'sort_order'=>1],
        ['id'=>3,'name'=>'Feedback',      'slug'=>'feedback','description'=>'Customer satisfaction',  'icon'=>'bi-star',             'sort_order'=>2],
    ],
    'fields' => [
        ['id'=>1, 'module_id'=>1,'name'=>'Subject', 'slug'=>'subject', 'type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>2, 'module_id'=>1,'name'=>'Status',  'slug'=>'status',  'type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>1,'options'=>['choices'=>['New','Open','In Progress','Resolved','Closed']]],
        ['id'=>3, 'module_id'=>1,'name'=>'Priority','slug'=>'priority','type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>2,'options'=>['choices'=>['Low','Normal','High','Urgent']]],
        ['id'=>4, 'module_id'=>1,'name'=>'Category','slug'=>'category','type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>3,'options'=>['choices'=>['Technical','Billing','General','Feature Request']]],
        ['id'=>5, 'module_id'=>1,'name'=>'Customer','slug'=>'customer','type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>4],
        ['id'=>6, 'module_id'=>2,'name'=>'Title',   'slug'=>'title',   'type'=>'text',    'required'=>1,'unique'=>1,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>7, 'module_id'=>2,'name'=>'Category','slug'=>'category','type'=>'dropdown','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>1,'options'=>['choices'=>['Getting Started','Troubleshooting','Billing','Security']]],
        ['id'=>8, 'module_id'=>2,'name'=>'Content', 'slug'=>'content', 'type'=>'textarea','required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>0,'sort'=>2],
        ['id'=>9, 'module_id'=>3,'name'=>'Ticket Ref','slug'=>'ticket_ref','type'=>'text',    'required'=>1,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>0],
        ['id'=>10,'module_id'=>3,'name'=>'Rating',    'slug'=>'rating',    'type'=>'number',  'required'=>1,'unique'=>0,'searchable'=>0,'in_list'=>1,'sort'=>1],
        ['id'=>11,'module_id'=>3,'name'=>'Comments',  'slug'=>'comments',  'type'=>'textarea','required'=>0,'unique'=>0,'searchable'=>0,'in_list'=>0,'sort'=>2],
        ['id'=>12,'module_id'=>3,'name'=>'Customer',  'slug'=>'customer',  'type'=>'text',    'required'=>0,'unique'=>0,'searchable'=>1,'in_list'=>1,'sort'=>3],
    ],
    'records' => [
        ['module_id'=>1,'data'=>['subject'=>'Cannot login to portal','status'=>'In Progress','priority'=>'High','category'=>'Technical','customer'=>'John Doe']],
        ['module_id'=>1,'data'=>['subject'=>'New mouse requested','status'=>'New','priority'=>'Low','category'=>'General','customer'=>'Bob Miller']],
        ['module_id'=>1,'data'=>['subject'=>'Invoice discrepancy','status'=>'Open','priority'=>'Urgent','category'=>'Billing','customer'=>'Jane Smith']],
        ['module_id'=>1,'data'=>['subject'=>'Add dark mode feature','status'=>'Resolved','priority'=>'Normal','category'=>'Feature Request','customer'=>'Carol White']],
        ['module_id'=>1,'data'=>['subject'=>'Slow VPN connection','status'=>'Closed','priority'=>'Normal','category'=>'Technical','customer'=>'Dave Brown']],
        ['module_id'=>2,'data'=>['title'=>'How to reset your password','category'=>'Getting Started','content'=>'Go to the login page and click Forgot Password.']],
        ['module_id'=>2,'data'=>['title'=>'VPN Setup Guide','category'=>'Troubleshooting','content'=>'Download VPN client from the IT portal and enter your credentials.']],
        ['module_id'=>2,'data'=>['title'=>'Understanding your invoice','category'=>'Billing','content'=>'Invoices are generated on the 1st of each month.']],
        ['module_id'=>3,'data'=>['ticket_ref'=>'TKT-001','rating'=>'5','comments'=>'Very quick resolution!','customer'=>'John Doe']],
        ['module_id'=>3,'data'=>['ticket_ref'=>'TKT-003','rating'=>'3','comments'=>'Took a bit long but resolved.','customer'=>'Jane Smith']],
    ],
    'widgets' => [
        ['title'=>'New Tickets',        'type'=>'count',    'module_id'=>1,'color'=>'#8b5cf6','width'=>3],
        ['title'=>'Avg Rating',         'type'=>'average',  'module_id'=>3, 'field_id'=>10, 'color'=>'#f59e0b','width'=>3],
        ['title'=>'KB Articles',        'type'=>'count',    'module_id'=>2,'color'=>'#10b981','width'=>3],
        ['title'=>'Feedback Received',  'type'=>'count',    'module_id'=>3,'color'=>'#3b82f6','width'=>3],
        ['title'=>'Tickets by Priority','type'=>'bar_chart','module_id'=>1, 'field_id'=>3, 'color'=>'#ef4444','width'=>6],
        ['title'=>'Status Overview',    'type'=>'pie_chart','module_id'=>1, 'field_id'=>2, 'color'=>'#8b5cf6','width'=>6],
    ],
];
