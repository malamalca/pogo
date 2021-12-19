<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Section Entity.
 *
 * @property string $id
 * @property string $category_id
 * @property \App\Model\Entity\Category $category
 * @property string $title
 * @property string $descript
 * @property int $sort_order
 * @property float $total
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Item[] $items
 * @property \App\Model\Entity\QtiesTag[] $qties_tags
 */
class Section extends Entity
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
        '*' => true,
        'id' => false,
    ];
}
