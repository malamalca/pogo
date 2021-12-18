<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Projects Model
 *
 * @method \App\Model\Entity\Project newEmptyEntity()
 * @method \App\Model\Entity\Project get(string $id, array $options = [])
 * @method \App\Model\Entity\Project patchEntity(\App\Model\Entity\Project $project, array $data)
 * @property \Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 * @property \Cake\ORM\Association\HasMany $Templates
 * @property \Cake\ORM\Association\HasMany $Variables
 * @property \Cake\ORM\Association\BelongsToMany $Users
 */
class ProjectsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('projects');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Categories', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Templates', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Variables', [
            'foreignKey' => 'project_id',
        ]);

        $this->hasMany('ProjectsUsers', [
            'foreignKey' => 'project_id',
        ]);

        $this->belongsToMany('Users', [
            'className' => 'Users',
            'foreignKey' => 'project_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'projects_users',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->allowEmptyString('no');

        $validator
            ->notEmptyString('title');

        $validator
            ->allowEmptyString('subtitle');

        $validator
            ->allowEmptyString('dat_place');

        $validator
            ->allowEmptyString('descript');

        $validator
            ->boolean('active')
            ->requirePresence('active', 'create')
            ->notEmptyString('active');

        $validator
            ->allowEmptyString('investor_title');

        $validator
            ->allowEmptyString('investor_address');

        $validator
            ->allowEmptyString('investor_zip');

        $validator
            ->allowEmptyString('creator_title');

        $validator
            ->allowEmptyString('creator_address');

        $validator
            ->allowEmptyString('creator_zip');

        $validator
            ->allowEmptyString('creator_post');

        $validator
            ->allowEmptyString('creator_person');

        $validator
            ->allowEmptyString('investor_post');

        $validator
            ->allowEmptyString('notes');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }

    /**
     * getTitle method
     *
     * @param string $id Project id
     * @return string
     */
    public function getTitle($id)
    {
        $ret = $this
            ->find()
            ->select(['title'])
            ->where(['id' => $id])
            ->map(function ($row) {

                return $row->title;
            })
            ->toArray();

        return reset($ret);
    }

    /**
     * Returns projects for specified user
     *
     * @param string $userId User id
     * @param bool $mapUserData Map user data
     * @return mixed
     */
    public function findForUser($userId, $mapUserData = true)
    {
        $query = $this->find();

        $query
            ->matching('ProjectsUsers', function ($q) use ($userId) {
                return $q->where(['ProjectsUsers.user_id' => $userId]);
            })
            ->order(['ProjectsUsers.archived', 'Projects.no DESC']);

        if ($mapUserData) {
            return $query->map(function ($row) {
                $row->userData = $row->_matchingData['ProjectsUsers'];
                unset($row->_matchingData);

                return $row;
            });
        } else {
            return $query;
        }
    }

    /**
     * Returns Project variables
     *
     * @param string $id Project id
     * @return array Project variables
     */
    public function getVariables($id)
    {
        $ret = [];

        $variables = Cache::read('variable.Project' . $id, 'site');
        if (!empty($variables)) {
            $ret = $variables;
        } else {
            $Variables = TableRegistry::get('Variables');
            $vars = $Variables->find()
                ->select(['id', 'name', 'value'])
                ->where(['project_id' => $id])
                ->all();

            foreach ($vars as $var) {
                $ret[$var->name] = ['id' => $var->id, 'value' => $var->value];
            }

            Cache::write('variable.Project' . $id, $ret, 'site');
        }

        return (array)$ret;
    }
}
