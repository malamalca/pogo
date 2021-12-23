<?php

    use Cake\Routing\Router;

    /*echo sprintf(
        '<div class="dashboard-project dashboard-project-%2$s" id="project-%1$s">%3$s</div>',
        $project->id,
        $project->archived ? 'archived' : 'active',
        $project->title
    );*/

?>

<div class="col s12 m6 card-project">
<div class="card horizontal">
    <div class="card-stacked">
        <div class="card-content">
            <img src="<?= Router::url(['action' => 'picture', $project->id, 'thumb', '_ext' => 'png']) ?>" class="circle" />
            <div class="card-head">
                <div><?= h($project->no) ?></div>
                <span class="card-title"><?= $this->Html->link($project->title, ['action' => 'view', $project->id]) ?></span>
            </div>
        </div>
    </div>
</div>
</div>
