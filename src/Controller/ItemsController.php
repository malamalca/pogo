<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Items Controller
 *
 * @property \App\Model\Table\ItemsTable $Items
 */
class ItemsController extends AppController
{
    /**
     * BeforeFilter event handler
     *
     * @param \Cake\Event\EventInterface $event Event interface
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (!empty($this->FormProtection)) {
            if (in_array($this->getRequest()->getParam('action'), ['edit'])) {
                $this->FormProtection->setConfig('unlockedFields', ['qties']);

                $this->FormProtection->setConfig('validate', false);
            }
        }
    }

    /**
     * View method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null
     */
    public function view($id = null)
    {
        $item = $this->Items->get($id, ['contain' => ['Qties']]);

        $this->Authorization->authorize($item);

        $this->set('item', $item);

        if ($this->request->is('ajax')) {
            $this->response = $this->response->withType('json');
        }
        $this->response = $this->response->withStringBody((string)json_encode($item));

        return $this->response;
    }

    /**
     * Edit method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($id) {
            $item = $this->Items->get($id, ['contain' => ['Qties']]);
        } else {
            $item = $this->Items->newEmptyEntity();
        }
        $this->Authorization->authorize($item);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $item = $this->Items->patchEntity($item, $this->request->getData());

            $result = $this->Items->save($item);

            // deleted qties
            $deleteList = $this->request->getData('qties_to_delete');
            if ($result && !empty($deleteList)) {
                $this->Items->Qties->deleteAll(['id IN' => $deleteList]);
                $item->qty = $this->Items->Qties->sumForItem($item->id);
                $this->Items->save($item);
            }

            if ($this->request->is('ajax')) {
                $errors = $item->getErrors();

                $this->response = $this->response->withType('json');
                $this->response = $this->response->withStringBody((string)json_encode([
                    'result' => (bool)$result,
                    'data' => $result,
                    'errors' => $errors,
                ]));

                return $this->response;
            }

            if ($result) {
                $this->Flash->success(__('The item has been saved.'));

                return $this->redirect(['controller' => 'Sections', 'action' => 'view', $item->section_id]);
            } else {
                $this->Flash->error(__('The item could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('item'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete', 'get']);
        $item = $this->Items->get($id);

        $this->Authorization->authorize($item);

        $result = $this->Items->delete($item);

        if ($this->request->is('ajax')) {
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody((string)json_encode(['result' => (bool)$result]));

            return $this->response;
        }

        if ($result) {
            $this->Flash->success(__('The item has been deleted.'));
        } else {
            $this->Flash->error(__('The item could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Reorder items
     *
     * @param string $id Item id
     * @param int $position New position inside section
     * @return void
     */
    public function reorder($id, $position)
    {
        $item = $this->Items->get($id);

        $this->Authorization->authorize($item, 'edit');

        if ($this->Items->reorder($item, $position)) {
            $this->autoRender = false;
        }
    }
}
