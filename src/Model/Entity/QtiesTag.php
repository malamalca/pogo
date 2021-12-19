<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * QtiesTag Entity
 *
 * @property string $id
 * @property string $project_id
 * @property string $category_id
 * @property string $section_id
 * @property string $item_id
 * @property string $qty_id
 * @property string $tag
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Section $section
 * @property \App\Model\Entity\Item $item
 * @property \App\Model\Entity\Qty $qty
 */
class QtiesTag extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<array-key, bool>
     */
    protected $_accessible = [
        'project_id' => true,
        'category_id' => true,
        'section_id' => true,
        'item_id' => true,
        'qty_id' => true,
        'tag' => true,
        'created' => true,
        'modified' => true,
        'project' => true,
        'category' => true,
        'section' => true,
        'item' => true,
        'qty' => true,
    ];
}
