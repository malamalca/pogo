<?php
$section_edit = [
    'title_for_layout' => __('Invite to participate'),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$project],
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'project_id', ['value' => $project->id]],
            ],

            'email' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'email',
                    'options' => [
                        'label' => __('Email') . ':',
                        'error' => __('Title is required field.'),
                    ],
                ],
            ],
            'privileges' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'role',
                    'options' => [
                        'type' => 'select',
                        'label' => [
                            'text' => __('Privileges') . ':',
                            'class' => 'active',
                        ],
                        'options' => [
                            15 => __('Reader'),
                            10 => __('Editor'),
                            5 => __('Owner'),
                        ],
                        'class' => 'browser-default',
                    ],
                ],
            ],

            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    'label' => __('Save'),
                ],
            ],
            'form_end' => [
                'method' => 'end',
                'parameters' => [],
            ],
        ],
    ],
];

echo $this->Lil->form($section_edit, 'ProjectsUsers.invite');

$this->Lil->jsReady('$("#title").focus()');
