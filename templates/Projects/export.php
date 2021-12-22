<?php
    use App\Lib\CurrentLocation;
    use App\Lib\MainMenu;

    $project = CurrentLocation::getProject();
    $projectsExport = [
        //'title_for_layout' => __('Project Export'),
        'title_for_layout' => (CurrentLocation::getProject())->title,
        'menu' => MainMenu::forProject($project, $this->getCurrentUser(), ['active' => 'export']),
        'form' => [
            'defaultHelper' => $this->Form,
            'pre' => '<div class="form">',
            'post' => '</div>',
            'lines' => [
                'form_start' => [
                    'method' => 'create',
                    'parameters' => [null, ['type' => 'GET']],
                ],
                'type' => [
                    'method' => 'control',
                    'parameters' => [
                        'type',
                        'options' => ['type' => 'hidden', 'value' => 'xls'],
                    ],
                ],

                'fs_title' => sprintf('<h2>%s</h2>', __('Basic options')),
                //'fs_basics_start' => sprintf('<fieldset><legend>%s</legend>', __('Basic options')),
                'fs_basics_start' => '<fieldset>',
                'qties' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'qties',
                        'options' => [
                            'type' => 'checkbox',
                            'value' => 'none',
                            'label' => __('Export Without Item Qties'),
                        ],
                    ],
                ],
                'prices' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'noprice',
                        'options' => [
                            'type' => 'checkbox',
                            'label' => __('Export Without Item Prices'),
                        ],
                    ],
                ],
                'categories' => $categories->isEmpty() ? null : [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'categories',
                        'options' => [
                            'type' => 'checkbox',
                            'label' => __('Export only selected Categories'),
                            'onclick' => '$("#filter-category-list").toggle();',
                            'autocomplete' => 'off',
                        ],
                    ],
                ],
                'cat-list-start' => '<div id="filter-category-list" style="display: none;">',
                'cat-list-end' => '</div>',
                'hashtags' => $tags->isEmpty() ? null : [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'hashtags',
                        'options' => [
                            'label' => __('Tags') . ':',
                            'type' => 'select',
                            'options' => $tags,
                            'multiple' => true,
                        ],
                    ],
                ],
                'fs_basics_end' => '</fieldset>',

                'submit' => [
                    'method' => 'submit',
                    'parameters' => [
                        'label' => __('Export'),
                    ],
                ],
                'form_end' => [
                    'method' => 'end',
                    'parameters' => [],
                ],
            ],
        ],
    ];

    foreach ($categories as $category) {
        $this->Lil->insertIntoArray(
            $projectsExport['form']['lines'],
            ['cat-' . $category->id => [
                'method' => 'control',
                'parameters' => [
                    'category[]',
                    [
                        'type' => 'checkbox',
                        'label' => $category->title,
                        'value' => $category->id,
                        'autocomplete' => 'off',
                    ],
                ],
            ]],
            ['before' => 'cat-list-end']
        );
    }

    echo $this->Lil->form($projectsExport);
