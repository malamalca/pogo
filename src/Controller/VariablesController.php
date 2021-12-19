<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Variables Controller
 *
 * @property \App\Model\Table\VariablesTable $Variables
 * @method \Cake\Datasource\ResultSetInterface|\Cake\ORM\ResultSet paginate($object = null, array $settings = [])
 */
class VariablesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Projects'],
        ];
        $variables = $this->paginate($this->Variables);

        $this->set(compact('variables'));
    }

    /**
     * View method
     *
     * @param string|null $id Variable id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $variable = $this->Variables->get($id, [
            'contain' => ['Projects', 'Qties'],
        ]);

        $this->set('variable', $variable);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $variable = $this->Variables->newEmptyEntity();
        if ($this->request->is('post')) {
            $variable = $this->Variables->patchEntity($variable, $this->request->getData());
            if ($this->Variables->save($variable)) {
                $this->Flash->success(__('The variable has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The variable could not be saved. Please, try again.'));
        }
        $projects = $this->Variables->Projects->find('list', ['limit' => 200]);
        $qties = $this->Variables->Qties->find('list', ['limit' => 200]);
        $this->set(compact('variable', 'projects', 'qties'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Variable id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $variable = $this->Variables->get($id, [
            'contain' => ['Qties'],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $variable = $this->Variables->patchEntity($variable, $this->request->getData());
            if ($this->Variables->save($variable)) {
                $this->Flash->success(__('The variable has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The variable could not be saved. Please, try again.'));
        }
        $projects = $this->Variables->Projects->find('list', ['limit' => 200]);
        $qties = $this->Variables->Qties->find('list', ['limit' => 200]);
        $this->set(compact('variable', 'projects', 'qties'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Variable id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $variable = $this->Variables->get($id);
        if ($this->Variables->delete($variable)) {
            $this->Flash->success(__('The variable has been deleted.'));
        } else {
            $this->Flash->error(__('The variable could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
