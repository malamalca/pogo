<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// hidden form

echo '<div id="view-section-edit-form" style="display: none;">';
echo $this->Form->create(null, [
    'url' => [
        'plugin' => false,
        'controller' => 'items',
        'action' => 'edit',
    ],
    'id' => 'ItemEditForm',
]);

print('<table class="view-section-item" id="tblitm0">');
print('<tr id="row-descript">');
    print('<td class="col-item col-item-delete">&nbsp;</td>');
    print('<td class="col-item col-item-order"><span class="handle">%1$s</span></td>' );
    printf(
        '<td class="col-item col-item-descript">%s</td>',
        $this->Form->control('descript', ['label' => false, 'div' => false, 'rows' => 1, 'id' => 'ItemDescript']) .
            $this->Form->control('id', ['type' => 'hidden', 'id' => 'ItemId']) .
            $this->Form->control('section_id', ['type' => 'hidden', 'value' => $section_id, 'id' => 'ItemSectionId']) .
            $this->Form->control('sort_order', ['type' => 'hidden', 'id' => 'ItemSortOrder']) .
            $this->Form->control('referer', ['type' => 'hidden', 'id' => 'ItemReferer'])
    );
    print('<td class="col-item col-item-unit">[Aux]</td>');
    printf('<td class="col-item col-item-qty">%s</td>', __('Qty'));
    print('<td class="col-item col-item-price"></td>');
    print('<td class="col-item col-item-total"></td>');
    print('</tr>');

////////////////////////////////////////////////////////////////////////////////////////////////////
    print('<tr><td colspan="7" class="row-qties"><ul>');

    print('<li class="row-qty">');
    print('<table class="editor-qties"><tr>');
    printf('<td class="col-item col-item-delete">%1$s</td>', $this->Html->image('/img/icon_trash.gif'));
    print('<td class="col-item col-item-order"><span class="qty-handle">&nbsp;</span></td>' );
    printf(
        '<td class="col-item col-item-descript">%s</td>',
        $this->Form->hidden('qties.1.id', ['class' => 'qties-id']) .
        $this->Form->hidden('qties.1.item_id', ['class' => 'qties-item_id']) .
        $this->Form->hidden('qties.1.sort_order', ['class' => 'qties-sort_order']) .
        $this->Form->text('qties.1.descript', ['class' => 'qties-descript'])
    );

    printf(
        '<td class="col-item col-item-unit">%s</td>',
        $this->Form->hidden('qties.1.aux_formula', ['class' => 'qties-aux_formula']) .
        $this->Form->text('qties.1.aux_value', ['class' => 'qties-aux_value right', 'autocomplete' => 'off'])
    );

    printf(
        '<td class="col-item col-item-qty">%s</td>',
        $this->Form->hidden('qties.1.qty_formula', ['class' => 'qties-qty_formula']) .
        $this->Form->text('qties.1.qty_value', ['class' => 'qties-qty_value right', 'autocomplete' => 'off'])
    );

// leafletjs map link
    print('<td class="col-item col-item-price"></td>');
    print('<td class="col-item col-item-total"></td>');
    print('</tr></table>');
    print('</li>');

    print('</ul></td></tr>');

////////////////////////////////////////////////////////////////////////////////////////////////////
    print('<tr id="row-calculation">');
    print('<td class="col-item col-item-delete">&nbsp;</td>');
    print('<td class="col-item col-item-order">&nbsp;</td>' );
    printf(
        '<td class="col-item col-item-descript">%s</td>',
        sprintf('<span id="view-section-add-qty">%s</span>', __('add item'))
    );

    printf(
        '<td class="col-item col-item-unit">%s</td>',
        $this->Form->text('unit', ['class' => 'center', 'id' => 'ItemUnit'])
    );

    print('<td class="col-item col-item-qty">&nbsp;</td>');

    printf(
        '<td class="col-item col-item-price">%s</td>',
        $this->Form->text('price', ['class' => 'right', 'id' => 'ItemPrice', 'autocomplete' => 'off'])
    );

    printf('<td class="col-item col-item-total">0</td>');
    print('</tr>');

////////////////////////////////////////////////////////////////////////////////////////////////////
    printf(
        '<tr class="row-item-submit">' .
        '<td colspan="2">&nbsp;</td>' .
        '<td colspan="1" class="editor-item-submit">' .
            '%1$s %2$s <span id="view-section-cancel">%3$s</span>' .
        '</td>' .
        '<td colspan="4">&nbsp;</td>' .
        '</tr>',
        $this->Form->submit(__('Save')),
        __('or'),
        __('Cancel')
    );

    print('</table>');

    echo $this->Form->end();
    echo '</div>';
