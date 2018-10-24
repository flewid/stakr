<?php
return [
    'adminPermission' => [
        'type' => 2,
        'description' => 'Админ permission',
    ],
    'userPermission' => [
        'type' => 2,
        'description' => 'Пользователь пермишн',
    ],
    'updateOwnModel' => [
        'type' => 2,
        'description' => 'Редактировать свою запись',
        'ruleName' => 'ownRule',
    ],
    2 => [
        'type' => 1,
        'description' => 'Пользователь',
        'children' => [
            'updateOwnModel',
            'userPermission',
        ],
    ],
    1 => [
        'type' => 1,
        'description' => 'Администратор',
        'children' => [
            'adminPermission',
            2,
        ],
    ],
];
