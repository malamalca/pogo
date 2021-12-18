<?php
use App\Lib\PogoExport;
use Cake\Routing\Router;

$section_view = [
    'title_for_layout' => sprintf(
        '<span id="view-section-title">%s</span>',
        h(chr(64 + $category->sort_order) . '. ' . $category->title)
    ) . ' ' .
        h(PogoExport::rome($section->sort_order) . '. ' . $section->title),
    'menu' => [
        'add' => [
            'title'   => __('Edit', true),
            'visible' => $this->getCurrentUser()->hasRole('editor', $category->project_id),
            'url'     => [
                'plugin'     => false,
                'controller' => 'sections',
                'action' => 'edit',
                $section->id,
            ]
        ],
        'delete' => [
            'title'   => __('Delete', true),
            'visible' => $this->getCurrentUser()->hasRole('editor', $category->project_id),
            'url'     => [
                'plugin'     => false,
                'controller' => 'sections',
                'action' => 'delete',
                $section->id,
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this section?')
            ]
        ],
    ],
    'panels' => [
        'descript' => empty($data['Section']['descript']) ? null : [
            'params' => ['id' => 'view-section-descript'],
            'html'   => nl2br(h($data['Section']['descript']))
        ],
        'header' => sprintf(
            '<table id="view-section-header"><tr>' .
            '<th class="col-item-delete">%1$s</th>' .
            '<th class="col-item-order">%2$s</th>' .
            '<th class="col-item-descript">%3$s</th>' .
            '<th class="col-item-unit">%4$s</th>' .
            '<th class="col-item-qty">%5$s</th>' .
            '<th class="col-item-price">%6$s</th>' .
            '<th class="col-item-total">%7$s</th>' .
            '</tr></table>',
            '&nbsp;',
            __('Ord.'),
            __('Description'),
            __('Unit'),
            __('Qty'),
            __('Price'),
            __('Total')
        )
    ]
];

$add_item_tpl =
    str_replace(
        '[[url_tpl]]',
        Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'add_tpl',
        'section'    => $section->id,
        'before' => '__order__'
        ]),
        str_replace(
            '[[url_add]]',
            Router::url([
            'plugin'     => false,
            'controller' => 'items',
            'action' => 'edit',
            'section'    => $section->id,
            'before' => '__order__'
            ]),
            '<div class="view-section-actions">'.
            '<a href="[[url_add]]" class="view-section-add-item" id="additm__order__"></a>'.
            '<a href="[[url_tpl]]" class="view-section-add-tpl-item" id="addtplitm__order__"></a>'.
            '</div>'
        )
    );

$delete_item_tpl =
    str_replace(
        '[[img]]',
        $this->Html->image('/img/icon_trash.gif'),
        str_replace(
            '[[url]]',
            Router::url([
            'plugin'     => false,
            'controller' => 'items',
            'action' => 'delete',
            '__id__',
            ]),
            '<a href="[[url]]" class="view-section-delete-item" style="display:none;">[[img]]</a>'
        )
    );

$copy_item_tpl =
    str_replace(
        '[[img]]',
        $this->Html->image('/img/icon_copy.gif'),
        str_replace(
            '[[url]]',
            Router::url([
            'plugin'     => false,
            'controller' => 'items',
            'action' => 'copy',
            '__id__',
            ]),
            '<a href="[[url]]" class="view-section-copy-item" style="display:none;">[[img]]</a>'
        )
    );

$preview_item_tpl =
    str_replace(
        '[[img]]',
        $this->Html->image('/img/icon_preview.gif'),
        str_replace(
            '[[url]]',
            Router::url([
            'plugin'     => false,
            'controller' => 'items',
            'action' => 'ontemplate',
            $category->project_id,
            '?' => ['filter' => ['item' => '__id__']]
            ]),
            '<a href="[[url]]" class="view-section-delete-item">[[img]]</a>'
        )
    );

$tbl_tpl = '<table class="view-section-item" id="tblitm%1$s"><tr>' .
    '<td class="col-item-delete">%7$s</td>' .
    '<td class="col-item col-item-order"><span class="handle">%1$s</span></td>' .
    '<td class="col-item col-item-descript">%2$s</td>' .
    '<td class="col-item col-item-unit">%3$s</td>' .
    '<td class="col-item col-item-qty">%4$s</td>' .
    '<td class="col-item col-item-price">%5$s</td>' .
    '<td class="col-item col-item-total">%6$s</td>' .
    '</tr>%8$s</table>';

$section_view['panels']['items']['params']['id'] = 'view-section-items';

// show add item only if user is editor
if ($this->getCurrentUser()->hasRole('editor', $category->project_id)) {
    $section_view['panels']['items']['lines'][] = str_replace('__order__', 1, $add_item_tpl);
}

$j = 2;
$total = 0;

$section_view['panels']['items']['lines']['items_start'] = '<ul id="view-section-sortable">';
if (!empty($section->items)) {
    $qtys = [
        'm^1' => 'm<sup>1</sup>',
        'm^2' => 'm<sup>2</sup>',
        'm^3' => 'm<sup>3</sup>'
    ];

    foreach ($section->items as $item) {
        $section_view['panels']['items']['lines'][] = sprintf('<li id="itm%s">', $item->id);

        $section_view['panels']['items']['lines'][] = sprintf($tbl_tpl,
            $item->sort_order,
            nl2br(h($item->descript)),
            isset($qtys[$item->unit]) ? $qtys[$item->unit] : h($item->unit),
            is_null($item->qty) ? "" : $this->Number->precision($item->qty, 2),
            is_null($item->price) ? "" : $this->Number->precision($item->price, 2),
            $item->price && !is_null($item->qty) ? $this->Number->precision(round($item->qty * $item->price, 2), 2) : "",

            // delete link
            $this->getCurrentUser()->hasRole('editor', $category->project_id) ?
                (str_replace('__id__', $item->id, $delete_item_tpl) /* . '<br />' . str_replace('__id__', $item->id, $copy_item_tpl)*/)
                :
                str_replace('__id__', $item->id, $preview_item_tpl),

            // additional fields
            ''
        );

        // show add item only if user is editor
        if ($this->getCurrentUser()->hasRole('editor', $category->project_id)) {
            $section_view['panels']['items']['lines'][] = str_replace('__order__', $j, $add_item_tpl);
        }

        $section_view['panels']['items']['lines'][] = '</li>';

        $total += round($item->qty * $item->price, 2);
        $j++;
    }
}

$section_view['panels']['items']['lines']['items_end'] = '</ul>';

$section_view['panels']['items']['lines']['footer'] = sprintf(
    '<table id="view-section-footer"><tr>' .
    '<th class="col-item-caption-total">%1$s:</th>' .
    '<th class="col-item-grand-total">%2$s</th>' .
    '</tr></table>',
    __('Grand Total'),
    $this->Number->precision($total, 2)
);

$section_view['panels']['items']['lines']['editor'] =
    $this->element('item_editor', [
        'tbl_tpl' => $tbl_tpl, 'item_editor' => [], 'section_id' => $section->id
    ]);

echo $this->Lil->panels($section_view);

echo $this->Html->script('jquery.autogrow-textarea');
echo $this->Html->script('item-editor');
echo $this->Html->script('item-list');
echo $this->Html->script('item-readonly-list');
?>


<script type="text/javascript">

$(document).ready(function() {
<?php
if ($this->getCurrentUser()->hasRole('editor', $category->project_id)) {
    ?>
    $("#view-section-sortable").ItemList({
        projectId: "<?php echo $category->project_id; ?>",
        itemUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'view',
        '__id__'
    ]); ?>",
        postUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'edit',
        '__id__'
    ]); ?>",
        reorderUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'reorder',
        '[[item_id]]',
        '[[position]]'
    ]); ?>",
        cloneUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'clone',
        '[[item_id]]',
        '[[position]]'
    ]); ?>",
        checkFormulaUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'fields',
        'action' => 'check_formula',
    ]); ?>",
        newItemTemplate: <?php echo json_encode(sprintf(
            '<li id="itm__id__">%s</li>',
            sprintf($tbl_tpl, "__order__", "", "", "", "", "", $delete_item_tpl, "") . $add_item_tpl
        )); ?>,
        modifiedMessage: "<?php echo __('Contents modified. Exit without saving changes?') ?>",
        confirmDeleteMessage: "<?php echo __('Are you sure you want to delete this item?') ?>"
    });
    <?php
} else {
    // no editor for users without editing righst
    ?>
    $("#view-section-sortable").ItemReadonyList({
        captionShow: "<?php echo __('Show'); ?>",
        popupTitleShapes: "<?php echo __('Shapes'); ?>",
        itemUrl: "<?php echo Router::url([
        'plugin'     => false,
        'controller' => 'items',
        'action' => 'view',
        '__id__',
        '_ext'        => 'json'
    ]); ?>"
    });
    <?php
}
?>
});
</script>
