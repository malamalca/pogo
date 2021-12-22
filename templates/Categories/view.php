<?php

use App\Lib\MainMenu;
use Cake\Routing\Router;

$category_view = [
    'title_for_layout' => h($category->title),
    'menu' => MainMenu::forCategory($category, $this->getCurrentUser(), ['active' => 'view']),
    'panels' => [
        'title' => [
            'lines' => [
                '<h2>' . __('Sections') . '</h2>',
            ],
        ],
        'sections' => [
            'params' => ['class' => 'dashboard-category'],
            'lines' => [],
        ],
    ],
];

/**
 * Convert number to roman
 *
 * @param int $N Number
 * @return string
 */
function rome($N)
{
    $natural_roman = [1000 => 'M', 500 => 'D', 100 => 'C', 50 => 'L', 10 => 'X', 5 => 'V', 1 => 'I'];
    $rn = '';
    foreach ($natural_roman as $key => $value) {
        while ($N >= $key) {
            $N -= $key;
            $rn .= $value;
        }
    }

    return str_replace(
        ['DCCCC', 'CCCC', 'LXXXX', 'XXXX', 'VIIII', 'IIII'],
        ['CM', 'CD', 'XC', 'XL', 'IX', 'IV'],
        $rn
    );
}

$category_view['panels']['sections']['lines'][] = '<ul id="view-category-sections">';
$j = 1;
$total = 0;
foreach ($sections as $section) {
    $category_view['panels']['sections']['lines'][] = sprintf(
        '<li class="dashboard-section" id="sec%s">',
        $section->id
    );
    $category_view['panels']['sections']['lines'][] = sprintf('<span class="numbering handle">%s.</span>', rome($j));
    $category_view['panels']['sections']['lines'][] = $this->Html->link($section->title, [
        'plugin' => false,
        'controller' => 'Sections',
        'action' => 'view',
        $section['id'],
    ]);
    $category_view['panels']['sections']['lines'][] = sprintf(
        '<span class="total"><span class="section-total">%1$s</span> %2$s</span>',
        $this->Number->precision((float)$section->total, 2),
        $this->Number->formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
    );
    $category_view['panels']['sections']['lines'][] = '</li>';

    $total += $section->total;
    $j++;
}
$category_view['panels']['sections']['lines'][] = '</ul>';
$category_view['panels']['sections']['lines'][] = sprintf(
    '<span class="total"><span class="category-total">%1$s</span> %2$s</span>',
    $this->Number->precision((float)$total, 2),
    $this->Number->formatter()->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
);

echo $this->Lil->panels($category_view, 'categories-view');
echo $this->Html->script('section-sortable');

$this->Lil->jsReady(
    '$("#view-category-sections").SectionSortable({' . PHP_EOL .
        'reorderSectionUrl: "' .
            Router::url([
                'plugin' => false,
                'controller' => 'Sections',
                'action' => 'reorder',
                '[[section_id]]',
                '[[position]]',
            ]) .
        '"' . PHP_EOL .
    '});'
);
