<?php
if (empty($projects)) {
    echo '<p><br />' .
        $this->Lil->link(__('Start using pogo.si - create your [first project].'), [0 => [
        [
            'action' => 'edit',
        ]]])
     . '<br /><br /><br /><br /></p>';

    echo '<p>' . __('Problems? Send us an email info@pogo.si') . '</p>';
    echo '<p>' . __('Your pogo.si team.') . '</p>';
} else {
    $filter = $this->getRequest()->getQuery();

    // FILTER by active
    $activeLink = $this->Html->link(
        empty($filter['archived']) ? __('Active') : __('Archived'),
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-active', 'data-target' => 'dropdown-active']
    );
    $popupActive = ['items' => [
        ['title' => __('Active'), 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['archived' => null])]],
        ['title' => __('Archived'), 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['archived' => 1])]],
    ]];

    $popupActive = $this->Lil->popup('active', $popupActive, true);

    $filterTitle = __('{0} Projects', [$activeLink]);

    $projectsIndex = [
        'title_for_layout' => $filterTitle,
        'menu' => [
            'add' => [
                'title' => __('Add'),
                'visible' => true,
                'url' => [
                    'plugin' => false,
                    'controller' => 'Projects',
                    'action' => 'edit',
                ],
            ],
        ],
        'actions' => ['lines' => [$popupActive]],
        'panels' => [
            'projects' => [
                'params' => ['id' => 'active-projects'],
                'lines' => [
                ],
            ],
        ],
    ];

    $archived = false;
    foreach ($projects as $project) {
        $title = implode(' - ', array_filter([$project->no, $project->title]));
        if (!empty($project->subtitle)) {
            $title .= '(' . h($project->subtitle) . ')';
        }
        $title = $this->Html->link($title, ['action' => 'view', $project->id]);

        $projectsIndex['panels']['projects']['lines'][] = sprintf(
            '<div class="dashboard-project dashboard-project-%2$s" id="project-%1$s">%3$s</div>',
            $project->id,
            $project->archived ? 'archived' : 'active',
            $title
        );
    }

    echo $this->Lil->panels($projectsIndex, 'Projects.index');
}
