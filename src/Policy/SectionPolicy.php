<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\ORM\TableRegistry;

/**
 * Section Policy Resolver
 */
class SectionPolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Section $section Section
     * @return bool
     */
    public function canView($authUser, $section)
    {
        if (isset($section->category)) {
            $category = $section->category;
        } else {
            /** @var \App\Model\Table\CategoriesTable $CategoriesTable */
            $CategoriesTable = TableRegistry::get('Categories');
            $category = $CategoriesTable->getCached($section->category_id);
        }

        return $authUser->hasRole('viewer', $category->project_id);
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Section $section Section
     * @return bool
     */
    public function canEdit($authUser, $section)
    {
        if (isset($section->category)) {
            $category = $section->category;
        } else {
            /** @var \App\Model\Table\CategoriesTable $CategoriesTable */
            $CategoriesTable = TableRegistry::get('Categories');

            $category = $CategoriesTable->getCached($section->category_id);
        }

        return $authUser->hasRole('editor', $category->project_id);
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Section $section Section
     * @return bool
     */
    public function canDelete($authUser, $section)
    {
        return $this->canEdit($authUser, $section);
    }
}
