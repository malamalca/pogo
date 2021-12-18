<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Category Policy Resolver
 */
class CategoryPolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Category $category Category
     * @return bool
     */
    public function canView($authUser, $category)
    {
        return $authUser->hasRole('viewer', $category->project_id);
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Category $category Category
     * @return bool
     */
    public function canEdit($authUser, $category)
    {
        return $authUser->hasRole('editor', $category->project_id);
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Category $category Category
     * @return bool
     */
    public function canDelete($authUser, $category)
    {
        return $authUser->hasRole('editor', $category->project_id);
    }
}
