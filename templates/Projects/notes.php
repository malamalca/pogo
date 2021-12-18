<?php
    $area_edit = [
        'title_for_layout' => __('Project Notes'),
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
                    'parameters' => ['id'],
                ],
                'referer' => [
                    'method' => 'hidden',
                    'parameters' => ['referer'],
                ],

                'notes' => [
                    'method' => 'control',
                    'parameters' => [
                        'field' => 'notes',
                        'options' => [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'materialize-textarea',
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
    echo $this->Lil->form($area_edit, 'Projects.notes');
