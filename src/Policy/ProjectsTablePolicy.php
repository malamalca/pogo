<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * ProjectsTable Policy Resolver
 */
class ProjectsTablePolicy
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
        $query->matching('ProjectsUsers', function ($q) use ($user) {
            return $q->where(['ProjectsUsers.user_id' => $user->id]);
        });

        return $query;
    }
}
