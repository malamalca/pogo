<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Item Policy Resolver
 */
class ItemPolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Item $item Item
     * @return bool
     */
    public function canView($authUser, $item)
    {
        return true;
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Item $item Item
     * @return bool
     */
    public function canEdit($authUser, $item)
    {
        return true;
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Item $item Item
     * @return bool
     */
    public function canDelete($authUser, $item)
    {
        return true;
    }
}
