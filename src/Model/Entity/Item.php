<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity.
 *
 * @property string $id
 * @property string $section_id
 * @property \App\Model\Entity\Section $section
 * @property int $sort_order
 * @property string $descript
 * @property string $unit
 * @property float $qty
 * @property float $price
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Field[] $fields
 * @property \App\Model\Entity\Qty[] $qties
 * @property \App\Model\Entity\QtiesTag[] $qties_tags
 */
class Item extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Convert shorthand units
     *
     * @param string $unit Unit
     * @return string
     */
    protected function _setUnit($unit)
    {
        switch ($unit) {
            case 'm1':
                return 'm^1';
            case 'm2':
                return 'm^2';
            case 'm3':
                return 'm^3';
            default:
                return $unit;
        }
    }
}
