<?php
use App\Lib\PogoExport;
use Cake\Routing\Router;

$project_view = [
    'title_for_layout' => h($project->title),
    'menu' => [
        'edit' => [
            'title' => __('Edit'),
            'visible' => $project->active && $this->getCurrentUser()->hasRole('admin', $project->id),
            'url' => [
                'controller' => 'Projects',
                'action' => 'edit',
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
        'delete' => [
            'title' => __('Delete'),
            'visible' => $project->active && $this->getCurrentUser()->hasRole('admin', $project->id),
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
            'visible' => $this->getCurrentUser()->hasRole('editor', $project->id),
            'url' => [
                'controller' => 'Projects',
                'action' => 'toggleArchive',
                $project->id,
            ],
            'params' => [
                'confirm' => __('Are You Sure?'),
            ],
        ],
        'add' => [
            'title' => __('Add Category'),
            'visible' => $project->active && $this->getCurrentUser()->hasRole('editor', $project->id),
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
            'url' => [
                'controller' => 'Projects',
                'action' => 'export',
                $project->id,
            ],
        ],
    ],
    'panels' => [
        'investor' => [
            'params' => ['id' => 'dashboard-investor'],
            'lines' => [
                sprintf('<h2>%s</h2>', __('Investor')),
                ['label' => __('Title'), 'text' => $project->investor_title],
                ['label' => __('Address'), 'text' => $project->investor_address],
                ['label' => __('Post'), 'text' => implode(' ', [$project->investor_zip, $project->investor_post])],
            ],
        ],
        'creator' => [
            'params' => ['id' => 'dashboard-creator'],
            'lines' => [
                sprintf('<h2>%s</h2>', __('Creator')),
                ['label' => __('Title'), 'text' => $project->creator_title],
                ['label' => __('Address'), 'text' => $project->creator_address],
                ['label' => __('Post'), 'text' => implode(' ', [$project->creator_zip, $project->creator_post])],
            ],
        ],
        'recap_title' => sprintf('<h2 id="dashboard-recap">%s</h2>', __('Project Recapitulation')),
        'main' => [
            'params' => ['class' => 'no-margin'],
        ],
    ],
];

$html = '';
if (!empty($categories)) {
    $html .= '<ul class="dashboard-categories">';
    $i = 1;
    $j = 1;
    foreach ($categories as $category) {
        $html .= sprintf('<li class="dashboard-category" id="cat%s">', $category->id);
        $html .= sprintf('<span class="numbering handle cat_handle">%s.</span>', chr(64 + $category->sort_order));
        $html .= $this->Html->link($category->title, [
            'plugin' => false,
            'controller' => 'Categories',
            'action' => 'view',
            $category->id,
        ]);

        $html .= '<ul class="dashboard-sections">';
        $total = 0;
        foreach ($category->sections as $section) {
            $html .= sprintf('<li class="dashboard-section" id="sec%s">', $section->id);
            $html .= sprintf('<span class="numbering handle sec_handle">%s.</span>', PogoExport::rome($j));

            $section_title = $section->title ? $section->title : __('n/a');
            $html .= $this->Html->link($section_title, [
                'plugin' => false,
                'controller' => 'Sections',
                'action' => 'view',
                $section->id,
                '?' => ['category' => $category->id],
            ]);
            $html .= sprintf(
                '<span class="total"><span class="section-total">%1$s</span> %2$s</span>',
                $this->Number->precision($section->total, 2),
                $this->Number->formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
            );
            $html .= '</li>';
            $total += $section->total;
            $j++;
        }
        $html .= '</ul>';

        $html .= sprintf(
            '<span class="total"><span class="category-total">%1$s</span> %2$s</span>',
            $this->Number->precision($total, 2),
            $this->Number->formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
        );

        $html .= '</li>';

        $i++;
    }
    $html .= '</ul>';
}

$project_view['panels']['main']['html'] = $html;
////////////////////////////////////////////////////////////////////////////////////////////////////

echo $this->Lil->panels($project_view, 'Projects.view');

////////////////////////////////////////////////////////////////////////////////////////////////////
echo $this->Html->script('category-sortable');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#MenuItemAddCategory").modalPopup({
            title: "<?= __('Add Category') ?>",
            onOpen: function() { $("input#title").focus(); }
        });

        $(".dashboard-categories").CategorySortable({
            reorderCategoryUrl: "<?= Router::url([
                'plugin' => false,
                'controller' => 'Categories',
                'action' => 'reorder',
                '[[category_id]]',
                '[[position]]',
            ]) ?>",
            reorderSectionUrl: "<?= Router::url([
                'plugin' => false,
                'controller' => 'Sections',
                'action' => 'reorder',
                '[[section_id]]',
                '[[position]]',
                '[[category_id]]',
            ]) ?>"
        });
    });

</script>
