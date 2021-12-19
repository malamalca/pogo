<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\CurrentLocation;
use Cake\ORM\TableRegistry;

/**
 * Sections Controller
 *
 * @property \App\Model\Table\SectionsTable $Sections
 */
class SectionsController extends AppController
{
    /**
     * View method
     *
     * @param string|null $id Section id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $section = $this->Sections->get($id, ['contain' => ['Items']]);

        /** @var \App\Model\Table\CategoriesTable $Categories */
        $Categories = TableRegistry::get('Categories');
        $category = $Categories->getCached($section->category_id);

        $this->Authorization->authorize($section);

        CurrentLocation::set($category->project_id, $category->id, $section->id);

        $this->set(compact('section', 'category'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Section id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($id) {
            $section = $this->Sections->get($id);
        } else {
            $categoryId = $this->getRequest()->getQuery('category');
            if (!is_string($categoryId)) {
                throw new \Cake\Http\Exception\NotAcceptableException();
            }

            $section = $this->Sections->newEmptyEntity();
            $section->category_id = $categoryId;
        }
        $category = $this->Sections->Categories->get($section->category_id);

        $this->Authorization->authorize($section);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $section = $this->Sections->patchEntity($section, $this->request->getData());
            if ($this->Sections->save($section)) {
                $this->Flash->success(__('The section has been saved.'));

                return $this->redirect(['action' => 'view', $section->id]);
            } else {
                $this->Flash->error(__('The section could not be saved. Please, try again.'));
            }
        }

        CurrentLocation::set($category->project_id, $section->category_id, $section->id);

        $this->set(compact('section', 'category'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Section id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete', 'get']);
        $section = $this->Sections->get($id);

        $this->Authorization->authorize($section);

        if ($this->Sections->delete($section)) {
            $this->Flash->success(__('The section has been deleted.'));
        } else {
            $this->Flash->error(__('The section could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'Categories', 'action' => 'view', $section->category_id]);
    }

    /**
     * Reorder method
     *
     * @param string $id Section id
     * @param int $position New position inside section
     * @param string $category_id Category Id
     * @return void
     */
    public function reorder($id, $position, $category_id)
    {
        $section = $this->Sections->get($id);

        $this->Authorization->authorize($section, 'edit');

        if ($this->Sections->reorder($section, $category_id, $position)) {
            $ret = [
                'previous' => TableRegistry::get('Categories')->get($section->category_id),
                'new' => TableRegistry::get('Categories')->get($category_id ?: $section->category_id),
            ];

            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody((string)json_encode($ret));

            $this->autoRender = false;
        }
    }
}
