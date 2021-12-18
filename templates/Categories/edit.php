<?php
$form = [
    'title_for_layout' =>
        $category->isNew() ? __('Add Category') : __('Edit Category'),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$category],
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'id'],
            ],
            'project_id' => [
                'method' => 'hidden',
                'parameters' => ['field' => 'project_id'],
            ],

            'title' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'title',
                    'options' => [
                        'type' => 'text',
                        'label' => __('Title') . ':',
                        'error' => __('Title is required field.'),
                        'autofocus' => true,
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

echo $this->Lil->form($form, 'Categories.edit');
