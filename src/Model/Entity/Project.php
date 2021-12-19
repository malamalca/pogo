<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Project Entity.
 *
 * @property string $id
 * @property string $no
 * @property string $title
 * @property string $subtitle
 * @property string $dat_place
 * @property string $descript
 * @property bool $active
 * @property string $investor_title
 * @property string $investor_address
 * @property string $investor_zip
 * @property string $creator_title
 * @property string $creator_address
 * @property string $creator_zip
 * @property string $creator_post
 * @property string $creator_person
 * @property string $investor_post
 * @property string $notes
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Category[] $categories
 * @property \App\Model\Entity\QtiesTag[] $qties_tags
 * @property \App\Model\Entity\Template[] $templates
 * @property \App\Model\Entity\Variable[] $variables
 * @property \App\Model\Entity\User[] $users
 */
class Project extends Entity
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
