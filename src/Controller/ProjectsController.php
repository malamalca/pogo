<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\CurrentLocation;
use App\Lib\PogoExport;
use Cake\ORM\TableRegistry;

/**
 * Projects Controller
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 */
class ProjectsController extends AppController
{
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $filter = $this->getRequest()->getQuery('archived', '0') === '1' ? ['active' => false] : ['active' => true];
        $projects = $this->Authorization->applyScope($this->Projects->find())
            ->where($filter)
            ->order('Projects.no DESC')
            ->all();
        $this->set(compact('projects'));
    }

    /**
     * View method
     *
     * @param string|null $id Project id.
     * @return void
     */
    public function view($id = null)
    {
        $project = $this->Projects->get($id);

        $this->Authorization->authorize($project);

        $categories = $this->Projects->Categories->findForProject($id);

        CurrentLocation::setProject($id);
        $this->set(compact('project', 'categories'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        if ($id) {
            $project = $this->Projects->get($id);
        } else {
            $project = $this->Projects->newEmptyEntity();
        }

        $this->Authorization->authorize($project);

        if ($this->getRequest()->is(['post', 'put'])) {
            $this->Projects->patchEntity($project, $this->request->getData());

            // link new project to a user who is logged in
            if ($project->isNew()) {
                $project->users = [0 => $this->getCurrentUser()->getOriginalData()];
            }

            if ($this->Projects->save($project, ['associated' => ['Users']])) {
                $this->Flash->success(__('Your project has been updated.'));

                return $this->redirect(['action' => 'view', $project->id]);
            }
            $this->Flash->error(__('Unable to update your project.'));
        }

        CurrentLocation::setProject($id);
        $this->set(compact('project'));
    }

    /**
     * toggleArchive method
     *
     * @param string $id Project id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function toggleArchive($id)
    {
        $project = $this->Projects->get($id);

        $this->Authorization->authorize($project, 'edit');

        $project->active = !$project->active;

        if ($this->Projects->save($project)) {
            $this->Flash->success(__('Your project has been updated.'));
        } else {
            $this->Flash->error(__('Unable to update your project.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Notes
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function notes($id = null)
    {
        $project = $this->Projects->get($id);

        $this->Authorization->authorize($project, 'edit');

        if ($this->request->is(['post', 'put'])) {
            $this->Projects->patchEntity($project, $this->request->getData());

            if ($this->Projects->save($project)) {
                $this->Flash->success(__('Your project has been updated.'));
            } else {
                $this->Flash->error(__('Unable to update your project.'));
            }
        }

        CurrentLocation::setProject($id);
        $this->set(compact('project'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null)
    {
        $project = $this->Projects->get($id);
        $this->Authorization->authorize($project);

        $this->Projects->delete($project);
        $this->Flash->success(__('Your project has been deleted.'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * admin_export method
     *
     * @param string $id Project id
     * @return void
     */
    public function export($id = null)
    {
        $project = $this->Projects->get($id);
        $this->Authorization->authorize($project);

        CurrentLocation::setProject($id);

        if (empty($this->request->getQuery('type'))) {
            CurrentLocation::setProject($id);

            $tags = TableRegistry::get('QtiesTags')->find()
                ->select()
                ->distinct('tag')
                ->where(['project_id' => $id])
                ->all();
            $this->set(compact('tags'));
        } else {
            // do a real export
            if (!in_array($this->request->getQuery('type'), ['xls'])) {
                throw new \Cake\Http\Exception\NotFoundException(__('Invalid export file type.'));
            }

            $filter = ['project' => $id];
            if (!empty($this->request->getQuery('category'))) {
                $filter['category'] = $this->request->getQuery('category');
            }
            if (!empty($this->request->getQuery('hashtags'))) {
                $filter['tag'] = (array)$this->request->getQuery('hashtags');
            }
            if ($this->request->getQuery('qties') == 'all') {
                $filter['qties'] = true;
            }
            if ($this->request->getQuery('noprice') == '1') {
                $filter['noprice'] = true;
            }

            $this->autoRender = false;
            $result = PogoExport::execute($this->request->getQuery('type'), $filter);

            if ($result !== true) {
                $this->Flash->error($result);
                $this->redirect($this->referer());
            }
        }
    }
}
