<?php
declare(strict_types=1);

namespace App\Lib;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;

class AppSidebar
{
    /**
     * Add admin sidebar elements.
     *
     * @param mixed $event Event object.
     * @param \ArrayObject $sidebar Sidebar object.
     * @access public
     * @return void
     */
    public static function setSidebar($event, $sidebar)
    {
        $currentUser = null;

        $eventSubject = $event->getSubject();

        if (is_subclass_of($eventSubject, \App\Controller\AppController::class)) {
            $currentUser = $event->getSubject()->getCurrentUser();
        }

        if (!$currentUser) {
            return;
        }

        $controller = $event->getSubject()->getRequest()->getParam('controller');
        $action = $event->getSubject()->getRequest()->getParam('action');
        $plugin = $event->getSubject()->getRequest()->getParam('plugin');

        $currentProjectId = CurrentLocation::getProject();
        $currentCategory = CurrentLocation::getCategory();
        $currentSection = CurrentLocation::getSection();

        if ($currentProjectId) {
            $projectTitle = Cache::remember($currentProjectId . '-title', function () use ($currentProjectId) {
                /** @var \App\Model\Table\ProjectsTable $Projects */
                $Projects = TableRegistry::get('Projects');

                return $Projects->getTitle($currentProjectId);
            });
            $event->getSubject()->set('admin_title', $projectTitle);
        }

        $sidebar['welcome'] = [
            'title' => $projectTitle ?? __('Dashboard'),
            'visible' => $currentUser->get('id') && empty($currentProjectId),
            'active' => empty($currentProjectId),
            'url' => [
                'plugin' => false,
                'controller' => 'Projects',
                'action' => 'index',
            ],
            'items' => [
                'projects' => [
                    'title' => __('Projects'),
                    'url' => [
                        'controller' => 'Projects',
                        'action' => 'index',
                    ],
                    'visible' => true,
                    'active' =>
                        in_array($controller, ['Projects']) &&
                        in_array($action, ['index']),
                ],
                'templates' => [
                    'title' => __('Templats'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'TplCategories',
                        'action' => 'index',
                    ],
                    'visible' => true,
                    'active' =>
                        in_array($controller, ['TplSections']) &&
                        in_array($action, ['index', 'edit']),
                ],
            ],
        ];

        // show current project
        $sidebar['frontpage'] = [
            'visible' => !empty($currentProjectId),
            'title' => $projectTitle ?? '',
            'url' => [
                'controller' => 'Projects',
                'action' => 'view',
                $currentProjectId,
            ],
            'active' => true,
            'class' => 'project-title',
            'items' => [
                'frontpage' => [
                    'visible' => true,
                    'title' => __('Front Page'),
                    'url' => [
                        'controller' => 'Projects',
                        'action' => 'view',
                        $currentProjectId,
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['Projects']) &&
                        $action == 'view',
                    'expand' => false,
                    'submenu' => [],
                ],
                'properties' => [
                    'visible' => $currentUser->hasRole('admin', $currentProjectId),
                    'title' => __('Properties'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'Projects',
                        'action' => 'edit',
                        $currentProjectId,
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['Projects']) &&
                        $action == 'edit',
                    'expand' => false,
                    'submenu' => [],
                ],
                'notes' => [
                    'visible' => $currentUser->hasRole('admin', $currentProjectId),
                    'title' => __('Notes'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'Projects',
                        'action' => 'notes',
                        $currentProjectId,
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['Projects']) &&
                        $action == 'notes',
                    'expand' => false,
                    'submenu' => [],
                ],
                'users' => [
                    'visible' => $currentUser->hasRole('admin', $currentProjectId),
                    'title' => __('Users'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'ProjectsUsers',
                        'action' => 'index',
                        $currentProjectId,
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['ProjectsUsers']),
                    'expand' => false,
                    'submenu' => [],
                ],
                /*'templates' => [
                    'visible' => true,
                    'title' => __('Templates'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'Templates',
                        'action' => 'index',
                        $currentProjectId
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['Templates']),
                    'expand' => false,
                    'submenu' => []
                ],
                'vars' => [
                    'visible' => true,
                    'title' => __('Variables'),
                    'url' => [
                        'plugin' => false,
                        'controller' => 'Variables',
                        'action' => 'index',
                        $currentProjectId
                    ],
                    'params' => [],
                    'active' => in_array($controller, ['Variables']),
                    'expand' => false,
                    'submenu' => []
                ]*/
                ],
        ];

        if ($currentProjectId) {
            /** @var \App\Model\Table\CategoriesTable $Categories */
            $Categories = TableRegistry::get('Categories');
            $cats = $Categories->findForProject($currentProjectId);

            foreach ($cats as $category) {
                $sidebar['category_' . $category->id] = [
                    'visible' => true,
                    'title' => h($category->title),
                    'active' => $currentCategory == $category->id,
                    'url' => [
                        'plugin' => false,
                        'controller' => 'Categories',
                        'action' => 'view',
                        $category->id,
                    ],
                    'items' => [],
                ];

                if ($sidebar['category_' . $category->id]['active']) {
                    $sidebar['frontpage']['active'] = false;

                    foreach ($category->sections as $section) {
                        $sidebar['category_' . $category->id]['items'][] = [
                            'visible' => true,
                            'active' => $currentSection == $section->id,
                            'title' => h($section->title),
                            'url' => [
                                'plugin' => false,
                                'controller' => 'Sections',
                                'action' => 'view',
                                $section->id,
                            ],
                        ];
                    }
                }
            }
        }

        $event->setResult(['sidebar' => $sidebar]);
    }
}
