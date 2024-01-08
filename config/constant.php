<?php

return [
    'admin_email' => env('FROM_EMAIL', ''),
    'app_name' => env('APP_NAME', ''),
    'daliy_task_limit' => env('DAILY_TASK_LIMIT', 5),
    'notification_messages' => [
        'priority_list' => 'Add 5 tasks that need to be accomplished today.',
        'gratitude' => 'Fill out the 5 things you are grateful for today.',
        'reminder' => 'Fill your Day End Report before leaving for the day.'
    ],
    'notification_time' => [
        'morning' => env('MORNING_PUSH_NOTIFICATION', '07:00'),
        'lunch' => env('LUNCH_PUSH_NOTIFICATION', '13:00'),
        'evening' => env('EVENING_PUSH_NOTIFICATION', '19:00'),
        'timediffrence' => env('NOTIFICATION_DIFFRENCE', '30'),
    ],
    'forcefully_user_logout'=>[
            'true'=> 1,
            'false'=> 0,
    ]
];
