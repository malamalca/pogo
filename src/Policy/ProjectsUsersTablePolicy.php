<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * ProjectsUsersTable Policy Resolver
 */
class ProjectsUsersTablePolicy
{
    /**
     * Contacts scope
     *
     * @param \App\Model\Entity\User $user User
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function scopeIndex($user, $query)
    {
        $query->where(['ProjectsUsers.user_id' => $user->id]);

        return $query;
    }
}
