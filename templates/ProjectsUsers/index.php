<?php

$usersIndex = [
    'title_for_layout' => __('Users on Project'),
    'menu' => [
        'add' => [
            'title' => __('Invite'),
            'visible' => $this->getCurrentUser()->hasRole('editor'),
            'url' => [
                'action' => 'invite',
                $project->id,
            ],
        ],
    ],
    'table' => [
        'parameters' => [
            'width' => '100%',
        ],
        'head' => ['rows' => [['columns' => [
            'icon' => '',
            'title' => __('Title'),
            'actions' => [],
        ]]]],
    ],
];

foreach ($projectsUsers as $projectUser) {
    switch ($projectUser->role) {
        case 5:
            $userIcon = 'star_border';
            break;
        case 7:
            $userIcon = 'person_outline';
            break;
        case 10:
            $userIcon = 'remove_red_eye';
            break;
        default:
            $userIcon = 'hourglass_empty';
    }
    $usersIndex['table']['body']['rows'][]['columns'] = [
        'icon' => sprintf('<i class="material-icons">%s</i>', $userIcon),
        'title' => [
            'html' => h($projectUser->user->name ?? $projectUser->email),
        ],
        'actions' => [
            'parameters' => ['class' => 'right-align'],
            'html' => $this->Lil->deleteLink($projectUser->id),
        ],
    ];
}

echo $this->Lil->index($usersIndex, 'ProjectsUsers.index');
