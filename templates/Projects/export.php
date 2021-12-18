<?php
    $projectsExport = [
        'title_for_layout' => __('Project Export'),
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

                'fs_basics_start' => sprintf('<fieldset><legend>%s</legend>', __('Basic options')),
                'qties' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'qties',
                        'options' => [
                            'type' => 'select',
                            'label' => __('Items') . ':',
                            'options' => [
                                'all' => __('with Quantites'),
                                'none' => __('without Quantites'),
                            ],
                        ],
                    ],
                ],
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

    echo $this->Lil->form($projectsExport);
