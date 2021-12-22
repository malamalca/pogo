<?php
declare(strict_types=1);

namespace App\Lib;

/*
 * Handy menu helper
 */

class MainMenu
{
    /**
     * Returns main menu for project views
     *
     * @param \App\Model\Entity\Project $project Project entity
     * @param \App\Model\Entity\User $currentUser Logged in user
     * @param array $options Options array
     * @return array
     */
    public static function forProject($project, $currentUser, $options = [])
    {
        if (empty($project) || $project->isNew()) {
            return [
                'home' => [
                    'url' => '/',
                ],
            ];
        }

        $menu = [
            'home' => [
                'url' => [
                    'controller' => 'Projects',
                    'action' => 'view',
                    $project->id,
                ],
            ],
            'edit' => [
                'title' => __('Edit'),
                'visible' => $project->active && $currentUser->hasRole('admin', $project->id),
                'active' => !empty($options['active']) && $options['active'] == 'edit',
                'url' => [
                    'controller' => 'Projects',
                    'action' => 'edit',
                    $project->id,
                ],
            ],
            'notes' => [
                'title' => __('Notes'),
                'visible' => $project->active && $currentUser->hasRole('admin', $project->id),
                'active' => !empty($options['active']) && $options['active'] == 'notes',
                'url' => [
                    'controller' => 'Projects',
                    'action' => 'notes',
                    $project->id,
                ],
            ],
            /*'clone' => [
                'title' => __('Clone'),
                'visible' => $this->getCurrentUser()->hasRole('editor', $project->id),
                'url'   => [
                    'controller' => 'Projects',
                    'action' => 'clone',
                    $project->id
                ],
                'params' => [
                    'confirm' => __('Are you sure you want to clone this project?')
                ]
            ],*/
            'add-category' => [
                'title' => __('Add Category'),
                'visible' => $project->active && $currentUser->hasRole('editor', $project->id),
                'active' => !empty($options['active']) && $options['active'] == 'add-category',
                'url' => [
                    'controller' => 'Categories',
                    'action' => 'edit',
                    '?' => ['project' => $project->id],
                ],
                'params' => [
                    'id' => 'MenuItemAddCategory',
                    //'onclick' => sprintf('popup("%s", $(this).attr("href"), 240); return false;', __('Add Category')),
                ],
            ],
            'export' => [
                'title' => __('Export to XLS'),
                'visible' => true,
                'active' => !empty($options['active']) && $options['active'] == 'export',
                'url' => [
                    'controller' => 'Projects',
                    'action' => 'export',
                    $project->id,
                ],
            ],
            'admin' => [
                'title' => __('Administration'),
                'visible' => true,
                'submenu' => [
                    'delete' => [
                        'title' => __('Delete'),
                        'visible' => $project->active && $currentUser->hasRole('admin', $project->id),
                        'url' => [
                            'controller' => 'Projects',
                            'action' => 'delete',
                            $project->id,
                        ],
                        'params' => [
                            'confirm' => __('Are you sure you want to delete this project?'),
                        ],
                    ],
                    'archive' => [
                        'title' => $project->active ? __('Archive') : __('Unarchive'),
                        'visible' => $currentUser->hasRole('editor', $project->id),
                        'url' => [
                            'controller' => 'Projects',
                            'action' => 'toggleArchive',
                            $project->id,
                        ],
                        'params' => [
                            'confirm' => __('Are You Sure?'),
                        ],
                    ],
                ],
            ],
        ];

        return $menu;
    }

    /**
     * Returns main menu for section views
     *
     * @param \App\Model\Entity\Section $section Section entity
     * @param \App\Model\Entity\Category $category Category entity
     * @param \App\Model\Entity\User $currentUser Logged in user
     * @param array $options Options array
     * @return array
     */
    public static function forSection($section, $category, $currentUser, $options = [])
    {
        $menu = [
            'home' => [
                'url' => [
                    'controller' => 'Sections',
                    'action' => 'view',
                    $section->id,
                ],
            ],
            'edit' => [
                'title' => __('Edit'),
                'visible' => $section->isNew() ? false : $currentUser->hasRole('editor', $category->project_id),
                'active' => !empty($options['active']) && $options['active'] == 'edit',
                'url' => [
                    'controller' => 'Sections',
                    'action' => 'edit',
                    $section->id,
                ],
            ],
            'delete' => [
                'title' => __('Delete'),
                'visible' => $section->isNew() ? false : $currentUser->hasRole('editor', $category->project_id),
                'url' => [
                    'controller' => 'Sections',
                    'action' => 'delete',
                    $section->id,
                ],
                'params' => [
                    'confirm' => __('Are you sure you want to delete this section?'),
                ],
            ],
        ];

        return $menu;
    }

    /**
     * Returns main menu for category views
     *
     * @param \App\Model\Entity\Category $category Category entity
     * @param \App\Model\Entity\User $currentUser Logged in user
     * @param array $options Options array
     * @return array
     */
    public static function forCategory($category, $currentUser, $options = [])
    {
        $menu = [
            'home' => [
                'url' => [
                    'controller' => 'Categories',
                    'action' => 'view',
                    $category->id,
                ],
            ],
            'add-section' => [
                'title' => __('Add Section', true),
                'visible' => $currentUser->hasRole('editor', $category->project_id),
                'active' => !empty($options['active']) && $options['active'] == 'add-section',
                'url' => [
                    'plugin' => false,
                    'controller' => 'Sections',
                    'action' => 'edit',
                    '?' => ['category' => $category->id],
                ],
                'params' => [
                    'onclick' => sprintf(
                        'popup("%s", $(this).attr("href"), 600, 600); return false;',
                        __('Add Section')
                    ),
                ],
            ],
            'edit' => [
                'title' => __('Edit', true),
                'visible' => $currentUser->hasRole('editor', $category->project_id),
                'active' => !empty($options['active']) && $options['active'] == 'edit',
                'url' => [
                    'plugin' => false,
                    'controller' => 'Categories',
                    'action' => 'edit',
                    $category->id,
                ],
                'params' => [
                    'onclick' => sprintf('popup("%s", $(this).attr("href"), 250); return false;', __('Edit Category')),
                ],
            ],
            'delete' => [
                'title' => __('Delete', true),
                'visible' => $currentUser->hasRole('editor', $category->project_id),
                'url' => [
                    'plugin' => false,
                    'controller' => 'Categories',
                    'action' => 'delete',
                    $category->id,
                ],
                'params' => [
                    'confirm' => __('Are you sure you want to delete this category?'),
                ],
            ],
        ];

        return $menu;
    }
}
