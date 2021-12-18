<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Project Policy Resolver
 */
class ProjectPolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canView($authUser, $project)
    {
        return $authUser->hasRole('viewer', $project->id);
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canEdit($authUser, $project)
    {
        return $authUser->hasRole('editor', $project->id);
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canDelete($authUser, $project)
    {
        return $authUser->hasRole('editor', $project->id);
    }

    /**
     * Authorize export action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canExport($authUser, $project)
    {
        return $authUser->hasRole('viewer', $project->id);
    }

    /**
     * Authorize view users action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canViewUsers($authUser, $project)
    {
        return $authUser->hasRole('editor', $project->id);
    }

    /**
     * Authorize edit users action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Project $project Project
     * @return bool
     */
    public function canEditUsers($authUser, $project)
    {
        return $authUser->hasRole('editor', $project->id);
    }
}
