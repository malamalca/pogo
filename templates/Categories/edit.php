<?php

use App\Lib\MainMenu;

$form = [
    'title_for_layout' =>
        $category->isNew() ? $project->title : $category->title,
    'menu' => $category->isNew() ?
        MainMenu::forProject($project, $this->getCurrentUser(), ['active' => 'add-category']) :
        MainMenu::forCategory($category, $this->getCurrentUser(), ['active' => 'edit']),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'page_title' => '<h2>' . ($category->isNew() ? __('Add Category') : __('Edit Category')) . '</h2>',
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
