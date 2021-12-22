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

        CurrentLocation::setProject($project);
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
                $currentUser = $this->getCurrentUser();
                if ($currentUser) {
                    $project->users = [0 => $currentUser->getOriginalData()];
                }
            }

            if ($this->Projects->save($project, ['associated' => ['Users']])) {
                $this->Flash->success(__('Your project has been updated.'));

                return $this->redirect(['action' => 'view', $project->id]);
            }
            $this->Flash->error(__('Unable to update your project.'));
        }

        CurrentLocation::setProject($project);
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

        CurrentLocation::setProject($project);
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
    public function export($id)
    {
        $project = $this->Projects->get($id);
        $this->Authorization->authorize($project);

        CurrentLocation::setProject($project);

        if (empty($this->request->getQuery('type'))) {
            $tags = TableRegistry::get('QtiesTags')->find()
                ->select()
                ->distinct('tag')
                ->where(['project_id' => $id])
                ->all();

            $categories = $this->Projects->Categories->findForProject($id);

            $this->set(compact('tags', 'categories'));
        } else {
            // do a real export
            if (!in_array($this->request->getQuery('type'), ['xls'])) {
                throw new \Cake\Http\Exception\NotFoundException(__('Invalid export file type.'));
            }

            $filter = ['project' => $id];
            if (!empty($this->request->getQuery('categories'))) {
                $filter['category'] = array_filter((array)$this->request->getQuery('category'));
            }
            if (!empty($this->request->getQuery('hashtags'))) {
                $filter['tag'] = (array)$this->request->getQuery('hashtags');
            }
            if (!empty($this->request->getQuery('protect'))) {
                $filter['password'] = $this->request->getQuery('passwd');
            }
            $filter['qties'] = $this->request->getQuery('qties') !== 'none';
            $filter['noprice'] = (bool)$this->request->getQuery('noprice');
            $filter['accentprice'] = (bool)$this->request->getQuery('accentprice');

            $this->autoRender = false;

            $exportType = $this->getRequest()->getQuery('type');
            if (!is_string($exportType)) {
                throw new \Cake\Http\Exception\NotAcceptableException();
            }
            $result = PogoExport::execute($exportType, $filter);

            if ($result !== true) {
                $this->Flash->error((string)$result);
                $this->redirect($this->referer());
            }
        }
    }
}
