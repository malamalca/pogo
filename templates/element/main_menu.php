<?php
    use Cake\Routing\Router;

    $badParamKeys = ['confirm', 'escape'];

    // process popup submenus
    if (!empty($main_menu)) {
        foreach ($main_menu as $itemKey => $item) {
            if (!empty($item['submenu'])) {
                printf('<ul id="%s" class="dropdown-content main-menu">', 'dropdown_' . $itemKey . '_' . $prefix);
                foreach ($item['submenu'] as $subItem) {
                    if (!empty($subItem)) {
                        $params = isset($subItem['params']) ? array_diff_key($subItem['params'], array_flip($badParamKeys)) : [];
                        if (isset($subItem['params']['confirm'])) {
                            $params['onclick'] = sprintf('return confirm("%s");', $subItem['params']['confirm']);
                        }
                        echo '<li class="nowrap">';
                        echo $this->Html->link($subItem['title'], $subItem['url'], $params);
                        echo '</li>';
                    }
                }
                print('</ul>');
            }
        }
    }
?>

<ul id="main-menu">
<?php
if (!empty($main_menu)) {
    if (!isset($main_menu['home'])) {
        printf('<li class="tab"><a href="%s" target="_self">&#8962;</a></li>', Router::url('/'));
    }

    foreach ($main_menu as $itemKey => $item) {
        if (!empty($item) && (!isset($item['visible']) || $item['visible'] === true)) {
            ?>
        <li<?= isset($item['active']) && $item['active'] ? ' class="active"' : '' ?>>
            <?php
            // remove bad keys
            $params = isset($item['params']) ? array_diff_key($item['params'], array_flip($badParamKeys)) : [];
            $params['target'] = '_self';

            if (isset($item['params']['confirm'])) {
                $params['onclick'] = sprintf('return confirm("%s");', $item['params']['confirm']);
            }

            $params['class'] = explode(' ', $params['class'] ?? '');

            if (isset($item['active']) && $item['active'] === true) {
                $params['class'][] = 'active';
            }

            if (isset($item['submenu'])) {
                $params['class'][] = 'dropdown-trigger';
                $params['data-target'] = 'dropdown_' . $itemKey . '_' . $prefix;
                $params['escape'] = false;
                $params['url'] = '#!';
                $item['title'] .= ' <i class="material-icons right">arrow_drop_down</i>';
            }

            if (!empty($params['class'])) {
                $params['class'] = implode(' ', $params['class']);
                $params['escape'] = false;
            } else {
                unset($params['class']);
            }

            if ($itemKey == 'home') {
                $item['title'] = '&#8962;';
            }

            echo $this->Html->link($item['title'], empty($item['url']) ? '#' : $item['url'], $params);
            ?>
        </li>
            <?php
        }
    }
}
?>
</ul>
