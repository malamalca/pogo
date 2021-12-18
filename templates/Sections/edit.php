<?php
$section_edit = [
    'title_for_layout' =>
        $section->isNew() ? __('Add Section') : __('Edit Section'),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$section]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'id']
            ],
            'category_id' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'category_id']
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'referer']
            ],

            'title' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'title',
                    'options' => [
                        'label' => __('Title') . ':',
                        'error' => __('Title is required field.'),
                    ]
                ]
            ],
            'descript' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'descript',
                    'options' => [
                        'type' => 'textarea',
                        'label' => __('Description') . ':',
                        'class' => 'materialize-textarea'
                    ]
                ]
            ],

            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    'label' => __('Save')
                ]
            ],
            'form_end' => [
                'method' => 'end',
                'parameters' => []
            ],
        ]
    ]
];

echo $this->Lil->form($section_edit, 'Sections.edit');

$this->Lil->jsReady('$("#title").focus()');
