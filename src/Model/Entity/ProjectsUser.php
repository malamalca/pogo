<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProjectsUser Entity.
 *
 * @property int $id
 * @property string $project_id
 * @property \App\Model\Entity\Project $project
 * @property string $user_id
 * @property \App\Model\Entity\User $user
 * @property int $role
 * @property string|null $email
 * @property string|null $accept_key
 * @property bool $archived
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class ProjectsUser extends Entity
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
}
