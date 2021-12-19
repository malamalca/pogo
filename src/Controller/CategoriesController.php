<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\CurrentLocation;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends AppController
{
    /**
     * View method
     *
     * @param string $id Category id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id)
    {
        $category = $this->Categories->getCached($id);
        $this->Authorization->authorize($category);

        CurrentLocation::set($category->project_id, $category->id);

        $sections = $this->Categories->Sections->findForCategory($category);

        $this->set(compact('category', 'sections'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Category id.
     * @return void|\Cake\Http\Response
     */
    public function edit($id = null)
    {
        if ($id) {
            $category = $this->Categories->get($id);
        } else {
            $projectId = $this->getRequest()->getQuery('project');
            if (!is_string($projectId)) {
                throw new \Cake\Http\Exception\NotAcceptableException();
            }

            $category = $this->Categories->newEmptyEntity();
            $category->project_id = $projectId;
        }

        $this->Authorization->authorize($category);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->Categories->patchEntity($category, $this->request->getData());
            if ($this->Categories->save($category)) {
                $this->Flash->success(__('The category has been saved.'));

                return $this->redirect(['controller' => 'Categories', 'action' => 'view', $category->id]);
            } else {
                $this->Flash->error(__('The category could not be saved. Please, try again.'));
            }
        }

        CurrentLocation::set($category->project_id, $category->id);

        $this->set(compact('category'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Category id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null)
    {
        $category = $this->Categories->get($id);

        $this->Authorization->authorize($category);

        if ($this->Categories->delete($category)) {
            $this->Flash->success(__('The category has been deleted.'));
        } else {
            $this->Flash->error(__('The category could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'Projects', 'action' => 'view', $category->project_id]);
    }

    /**
     * Reorder method
     *
     * @param string $id Category id
     * @param int $position New position inside category
     * @return void|\Cake\Http\Response
     */
    public function reorder($id, $position = null)
    {
        $category = $this->Categories->get($id);
        $this->Authorization->authorize($category, 'edit');

        // http://localhost/pogo-new/categories/reorder/935887b0-c0f3-41f0-b335-e0b3b0c77b9c/1

        if (!empty($id) && $this->Categories->reorder($id, $position)) {
            $this->autoRender = false;

            return $this->redirect(['controller' => 'Categories', 'action' => 'view', $id]);
        }
    }
}
