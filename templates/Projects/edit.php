<?php
    use App\Lib\CurrentLocation;
    use App\Lib\MainMenu;

    $editProject = [
        'title_for_layout' => (CurrentLocation::getProject())->title ?? __('Add a Project'),
            //$project->isNew() ? __('Add a Project') : __('Project Properties'),
        'menu' => MainMenu::forProject(CurrentLocation::getProject(), $this->getCurrentUser(), ['active' => 'edit']),
        'form' => [
            'defaultHelper' => $this->Form,
            'pre' => '<div class="form">',
            'post' => '</div>',
            'lines' => [
                'page_title' => '<h2>' . __('Project Properties') . '</h2>',
                'form_start' => [
                    'method' => 'create',
                    'parameters' => [$project],
                ],
                'id' => [
                    'method' => 'hidden',
                    'parameters' => ['id'],
                ],
                'referer' => [
                    'method' => 'hidden',
                    'parameters' => ['referer'],
                ],
                'active' => [
                    'method' => 'hidden',
                    'parameters' => ['active', ['default' => true]],
                ],

                'fs_basics_start' => sprintf('<fieldset>'),
                'no' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'no',
                        'options' => [
                            'label' => __('No') . ':',
                        ],
                    ],
                ],
                'title' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'title',
                        'options' => [
                            'label' => __('Name') . ':',
                            'error' => __('Project name is required.'),
                            'class' => 'big',
                        ],
                    ],
                ],
                'subtitle' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'subtitle',
                        'options' => [
                            'label' => __('Subtitle') . ':',
                        ],
                    ],
                ],
                'description' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'descript',
                        'options' => [
                            'type' => 'textarea',
                            'class' => 'materialize-textarea',
                            'label' => __('Description') . ':',
                            'error' => __('Project description is required.'),
                        ],
                    ],
                ],
                'dat_place' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'dat_place',
                        'options' => [
                            'label' => __('Date and place') . ':',
                        ],
                    ],
                ],
                'fs_basics_end' => '</fieldset>',

                'fs_invest_start' => sprintf('<fieldset><legend>%s</legend>', __('Investor data')),
                'investor_title' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'investor_title',
                        'options' => [
                            'label' => __('Name or title') . ':',
                        ],
                    ],
                ],
                'investor_address' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'investor_address',
                        'options' => [
                            'label' => __('Address') . ':',
                        ],
                    ],
                ],
                'investor_zip' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'investor_zip',
                        'options' => [
                            'label' => __('ZIP') . ':',
                        ],
                    ],
                ],
                'investor_post' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'investor_post',
                        'options' => [
                            'label' => __('Post') . ':',
                        ],
                    ],
                ],
                'fs_invest_end' => '</fieldset>',

                'fs_creator_start' => sprintf('<fieldset><legend>%s</legend>', __('Creator data')),
                'creator_person' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'creator_person',
                        'options' => [
                            'label' => __('Project Leader') . ':',
                            'default' => $this->getCurrentUser()->get('name'),
                        ],
                    ],
                ],
                'creator_title' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'creator_title',
                        'options' => [
                            'label' => __('Name or title') . ':',
                        ],
                    ],
                ],
                'creator_address' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'creator_address',
                        'options' => [
                            'label' => __('Address') . ':',
                        ],
                    ],
                ],
                'creator_zip' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'creator_zip',
                        'options' => [
                            'label' => __('ZIP') . ':',
                        ],
                    ],
                ],
                'creator_post' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'creator_post',
                        'options' => [
                            'label' => __('Post') . ':',
                        ],
                    ],
                ],
                'fs_creator_end' => '</fieldset>',

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
    echo $this->Lil->form($editProject, 'Projects.edit');
