<?php
use App\Lib\CurrentLocation;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Routing\Router;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= strip_tags($this->fetch('title')) ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('main.css') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>

    <?= $this->Html->script('/lil/js/jquery.min.js') ?>
    <?= $this->Html->script('/js/vendor/Materialize/bin/materialize.min.js') ?>
    <?= $this->Html->script('/lil/js/lil_float.js') ?>
    <?= $this->Html->script('/lil/js/lil_date.js') ?>
    <?= $this->Html->script('modalPopup.js') ?>
    <?= $this->Html->script('jquery-ui.min.js') ?>

    <?= $this->fetch('script') ?>
</head>
<body>
    <header>
        <?php
            /** Determine project title */
            $currentProject = CurrentLocation::getProject();
            if ($currentProject) {
                $currentProjectLink = $this->Html->link(
                    $currentProject->title,
                    ['controller' => 'Projects', 'action' => 'view', $currentProject->id],
                    ['escape' => false, 'class' => 'brand-logo left']
                );
            }

            /** Current user link and popup */
            $currentUser = $this->getCurrentUser();
            if ($currentUser) {
                $currentUserLink = $this->Html->link(
                    $this->getCurrentUser()->get('name'),
                    '#!',
                    ['escape' => false, 'class' => 'user-avatar dropdown-trigger', 'data-target' => 'current-user-actions']
                );
        ?>
                <ul id="current-user-actions" class="dropdown-content">
                    <li><?= $this->Html->link(__('Settings'), ['plugin' => false, 'controller' => 'Users', 'action' => 'properties']) ?></li>
                    <li class="divider"></li>
                    <li><?= $this->Html->link(__('Logout'), ['plugin' => false, 'controller' => 'Users', 'action' => 'logout']) ?></li>
                </ul>
        <?php
            } // currentUser

            /** Breadcrumbs */
            $breadCrumbs = [];
            $breadCrumbs[] = $this->Html->link('Pogo.si', '/', ['class' => 'breadcrumb']);
            if ($currentCategory = CurrentLocation::getCategory()) {
                $breadCrumbs[] = $this->Html->link(
                    $currentProject->title,
                    ['controller' => 'Projects', 'action' => 'view', $currentCategory->project_id],
                    ['class' => 'breadcrumb']
                );
            }
            if ($currentCategory && $section = CurrentLocation::getSection()) {
                $breadCrumbs[] = $this->Html->link(
                    $currentCategory->title,
                    ['controller' => 'Categories', 'action' => 'view', $currentCategory->id],
                    ['class' => 'breadcrumb']
                );
            }
        ?>

        <nav class="">
            <div class="nav-wrapper">
                <div class="row">
                    <div class="nav-title-sub col s9 no-wrap truncate"><?= implode(PHP_EOL, $breadCrumbs) ?></div>
                    <div class="col s3 right-align no-wrap truncate"><?= $currentUserLink ?></div>
                </div>
                <div class="brand-logo no-wrap truncate"><?= $this->fetch('title') ?? 'POGO.si' ?></div>

                <?= $this->element('main_menu', ['prefix' => 'top']) ?>
            </div>
        </nav>
    </header>

    <!-- Contents -->
    <main>
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
            <br /><br />
        </div>
    </main>

    <footer>
    </footer>
    <script type="text/javascript">
        <?php
            //lilFloat settings should be made before $(document).ready();
            $formatter = $this->Number->formatter();
        ?>

        lilFloatSetup.decimalSeparator = "<?= $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL); ?>";
        lilFloatSetup.thousandsSeparator = "<?= $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL); ?>";
        //lilFloatSetup.thousandsSeparator = "";

        $(document).ready(function(){
            M.AutoInit();

            $(".sidenav-avatar, .sidenav-user-title").on("click", function(e) {
                $("#user-settings").toggle();
            });

            <?= $this->Lil->jsReadyOut(); ?>
        });
    </script>
</body>
</html>
