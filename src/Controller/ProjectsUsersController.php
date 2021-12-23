<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\InviteUserForm;
use App\Lib\CurrentLocation;
use Cake\ORM\TableRegistry;

/**
 * ProjectsUsers Controller
 *
 * @property \App\Model\Table\ProjectsUsersTable $ProjectsUsers
 * @method \Cake\Datasource\ResultSetInterface|\Cake\ORM\ResultSet paginate($object = null, array $settings = [])
 */
class ProjectsUsersController extends AppController
{
    /**
     * Index method
     *
     * @param string $projectId Project id
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index($projectId)
    {
        /** @var \App\Model\Table\ProjectsTable $Projects */
        $Projects = TableRegistry::getTableLocator()->get('Projects');

        $project = $Projects->get($projectId);

        $this->Authorization->authorize($project, 'viewUsers');

        CurrentLocation::set($project);

        $projectsUsers = $this->ProjectsUsers->find()
            ->where(['project_id' => $projectId])
            ->contain(['Projects', 'Users'])
            ->order('Projects.no DESC')
            ->all();

        $this->set(compact('projectsUsers', 'project'));
    }

    /**
     * Invite user method
     *
     * @param string $projectId Projects User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function invite($projectId)
    {
        /** @var \App\Model\Entity\Project $project */
        $project = $this->ProjectsUsers->Projects->get($projectId);
        $this->Authorization->authorize($project, 'editUsers');

        CurrentLocation::set($project);

        $form = new InviteUserForm();
        if ($this->request->is(['post', 'put'])) {
            if ($form->execute($this->request->getData())) {
                $this->Flash->success(__('The invitation has been sent.'));
                $this->redirect(['action' => 'index', $project->id]);
            } else {
                $this->Flash->error(__('There was a problem sending invitation. User likely already exists.'));
            }
        }

        $this->set(compact('project'));
    }

    /**
     * Accept Invite user method
     *
     * @param string $acceptKey Accept Key
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function acceptInvitation($acceptKey)
    {
        $this->Authorization->skipAuthorization();

        /** @var \App\Model\Entity\ProjectsUser $projectsUser */
        $projectsUser = $this->ProjectsUsers->find()
            ->select()
            ->where([
                'accept_key' => $acceptKey,
                'user_id IS' => null,
            ])
            ->first();

        if (!empty($projectsUser)) {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                throw new \Cake\Datasource\Exception\RecordNotFoundException();
            }

            // check if user is already colaborating on this project
            /** @var \App\Model\Entity\ProjectsUser $exists */
            $exists = $this->ProjectsUsers->find()
                ->select()
                ->where([
                    'project_id' => $projectsUser->project_id,
                    'user_id' => $currentUser->id,
                ])
                ->first();

            if (!empty($exists)) {
                $this->Flash->error(__('You are already colaboration on this project!'));
                $this->redirect(['action' => 'index', $projectsUser->project_id]);
            } else {
                // link user to project and reset invitation data
                $projectsUser->user_id = $currentUser->id;
                $projectsUser->accept_key = null;
                $projectsUser->email = null;
                $this->ProjectsUsers->save($projectsUser);

                $this->Flash->success(__('You accepted the invitation to colaborate on this project!'));
                $this->redirect(['action' => 'view', $projectsUser->project_id]);
            }
        } else {
            $this->Flash->error(__('Invalid Invitation Key!'));
            $this->redirect('/');
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Projects User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete', 'get']);

        $projectsUser = $this->ProjectsUsers->get($id);
        $project = $this->ProjectsUsers->get($projectsUser->project_id);
        $this->Authorization->authorize($project, 'editUsers');

        if ($this->ProjectsUsers->delete($projectsUser)) {
            $this->Flash->success(__('The projects user has been deleted.'));
        } else {
            $this->Flash->error(__('The projects user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
