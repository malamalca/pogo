<?php
    use Cake\Routing\Router;
?>
<h2>Participate in "<?= h($project->title) ?>"</h2>
<p>&nbsp;</p>
<p>This is your invite to participate on pogo project.</p>
<p>To accept this invitation please click on this
    <a href="<?= Router::url(['controller' => 'ProjectsUsers', 'action' => 'acceptInvitation', $projectsUser->accept_key], true) ?>">invitation link</a></p>
<p>&nbsp;</p>
<p>Regards, Pogo Team</p>

