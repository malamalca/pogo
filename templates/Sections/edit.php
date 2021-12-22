<?php

use App\Lib\MainMenu;
use App\Lib\PogoExport;

$section_edit = [
    'title_for_layout' =>
        $section->isNew() ? $category->title : h(PogoExport::rome($section->sort_order) . '. ' . $section->title),
        //$section->isNew() ? __('Add Section') : __('Edit Section'),
    'menu' => $section->isNew() ?
        MainMenu::forCategory($category, $this->getCurrentUser(), ['active' => 'add-section']) :
        MainMenu::forSection($section, $category, $this->getCurrentUser(), ['active' => 'edit']),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'page_title' => '<h2>' . ($section->isNew() ? __('Add Section') : __('Edit Section')) . '</h2>',
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

            'fs_start' => '<fieldset>',

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

            'fs_end' => '</fieldset>',

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
