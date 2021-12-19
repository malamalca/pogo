<?php
declare(strict_types=1);

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class InviteUserForm extends Form
{
    /**
     * Build schema
     *
     * @param \Cake\Form\Schema $schema Schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('email', ['type' => 'string'])
            ->addField('privileges', ['type' => 'number']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->minLength('name', 10)
            ->email('email');

        return $validator;
    }

    /**
     * Execute form
     *
     * @param array $data Data array
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        /** @var \App\Model\Table\ProjectsUsersTable $ProjectsUsers */
        $ProjectsUsers = TableRegistry::get('ProjectsUsers');

        /** @var \App\Model\Table\UsersTable $Users */
        $Users = TableRegistry::get('Users');

        // try to find user with specified data

        /** @var \App\Model\Entity\User $user */
        $user = $Users->find()->select()->where(['email' => $data['email']])->first();

        if (!empty($user)) {
            /** @var \App\Model\Entity\ProjectsUser $projectsUser */
            $projectsUser = $ProjectsUsers->find()
                ->select()
                ->where(['project_id' => $data['project_id'], 'user_id' => $user->id])
                ->first();

            // user on specified project already exists
            if (!empty($projectsUser)) {
                return false;
            }
        } else {
            // try to find existing invitation that user did not accept yet
            /** @var \App\Model\Entity\ProjectsUser $projectsUser */
            $projectsUser = $ProjectsUsers->find()
                ->select()
                ->where(['email' => $data['email'], 'user_id IS' => null])
                ->first();
        }

        if (empty($projectsUser)) {
            // add new user (invitation) to projects_users
            $projectsUser = $ProjectsUsers->newEmptyEntity();
            $projectsUser = $ProjectsUsers->patchEntity($projectsUser, $data);
            $projectsUser->accept_key = uniqid();

            $ProjectsUsers->save($projectsUser);
        }

        if (!empty($projectsUser)) {
            /** @var \App\Model\Table\ProjectsTable $Projects */
            $Projects = TableRegistry::get('Projects');
            $project = $Projects->get($projectsUser->project_id);

            // send new invitation
            $ret = $this->_sendInvitationEmail($project, $projectsUser);

            return (bool)$ret;
        } else {
            return false;
        }
    }

    /**
     * Send invitation email
     *
     * @param \App\Model\Entity\Project $project Projects entity.
     * @param \App\Model\Entity\ProjectsUser $projectsUser Projects User entity.
     * @return array
     */
    private function _sendInvitationEmail($project, $projectsUser)
    {
        $mailer = new Mailer();
        $mailer->setEmailFormat('html')
            ->setTo($projectsUser->email)
            ->setSubject(__('Project Participation Invite'))
            ->setViewVars(['project' => $project, 'projectsUser' => $projectsUser])
            ->viewBuilder()
                ->setTemplate('invite');

        return $mailer->deliver();
    }
}
