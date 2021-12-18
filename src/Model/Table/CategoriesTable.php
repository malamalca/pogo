<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Lib\CurrentLocation;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @method \App\Model\Entity\Category get(string $id)
 * @method \App\Model\Entity\Category patchEntity(\App\Model\Entity\Category $category, array $data)
 * @method \App\Model\Entity\Category newEmptyEntity()
 * @property \Cake\ORM\Association\BelongsTo $Projects
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 * @property \App\Model\Table\SectionsTable $Sections
 */
class CategoriesTable extends Table
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

        $this->setTable('categories');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'category_id',
        ]);
        $this->hasMany('Sections', [
            'foreignKey' => 'category_id',
            'dependent' => true,
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
            ->notEmptyString('title');

        /*$validator
            ->integer('sort_order')
            ->requirePresence('sort_order', 'create')
            ->notEmpty('sort_order');

        $validator
            ->decimal('total')
            ->requirePresence('total', 'create')
            ->notEmpty('total');*/

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
        $rules->add($rules->existsIn(['project_id'], 'Projects'));

        return $rules;
    }

    /**
     * AfterSave Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Category $category Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $category, ArrayObject $options)
    {
        if (empty($options['duplicate'])) {
            if ($category->isNew()) {
                if (empty($category->sort_order)) {
                    $query = $this->find();
                    $order = $query
                        ->select(['max_order' => $query->func()->max('sort_order')])
                        ->where([
                            'project_id' => $category->project_id,
                            'NOT' => ['id' => $category->id],
                        ])
                        ->first()
                        ->toArray();

                    $category->sort_order = $order['max_order'] + 1;
                    $this->save($category);
                } else {
                    $this->updateAll(
                        ['sort_order' => new QueryExpression('sort_order + 1')],
                        [
                            'project_id' => $category->project_id,
                            'sort_order >=' => $category->sort_order,
                            'id <>' => $category->id,
                        ]
                    );
                }
            }
        }

        Cache::delete('category-' . $category->id);
        Cache::delete('project-categories-' . $category->project_id);

        /*if ($category->isDirty('total')) {
            $query = $this->find();
            $project_total = $query
                ->select(['project_total' => $query->func()->sum('total')])
                ->where(['project_id' => $category->project_id])
                ->first()
                ->toArray();

            $project = $this->Projects->get($category->project_id);
            $project->total = $project_total['project_total'];
            $this->Projects->save($project);
        }*/
    }

    /**
     * AfterDelete Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Category $category Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterDelete(Event $event, EntityInterface $category, ArrayObject $options)
    {
        Cache::delete('category-' . $category->id);
        Cache::delete('project-categories-' . $category->project_id);
    }

    /**
     * Returns all categories for specified project
     *
     * @param string|null $project_id Project id
     * @return array
     */
    public function findForProject($project_id = null)
    {
        $cats = Cache::remember('project-categories-' . $project_id, function () use ($project_id) {
            $ret = $this->find()
                ->select()
                ->where(['Categories.project_id' => $project_id])
                ->order('Categories.sort_order')
                ->contain(['Sections'])
                ->all();

            return $ret;
        });

        return $cats;
    }

    /**
     * reorder method
     *
     * @param string $id Category id.
     * @param int $new_pos New position inside project.
     * @return bool
     */
    public function reorder($id = null, $new_pos = null)
    {
        $category = $this->get($id);
        if (!empty($category)) {
            $delta = '+'; // moving up
            if ($category->sort_order < $new_pos) {
                $delta = '-'; // moving down
            }

            // update all for new
            $this->updateAll(
                ['sort_order' => new QueryExpression('sort_order ' . $delta . ' 1')],
                [
                    'project_id' => $category->project_id,
                    (new QueryExpression())->between(
                        'sort_order',
                        min($category->sort_order, $new_pos),
                        max($category->sort_order, $new_pos)
                    ),
                ]
            );

            // update sorted item
            $this->updateAll(['sort_order' => $new_pos], ['id' => $id]);

            Cache::delete('project-categories-' . $category->project_id);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Recalculate
     *
     * @param string $categoryId Category id.
     * @return void
     */
    public function recalc($categoryId)
    {
        $query = TableRegistry::get('Sections')->find();
        $category_total = $query
            ->select(['category_total' => $query->func()->sum('total')])
            ->where(['category_id' => $categoryId])
            ->first()
            ->toArray();

        $category = $this->get($categoryId);
        $category->total = $category_total['category_total'];
        $this->save($category);
    }

    /**
     * Get cached entiry
     *
     * @param string $categoryId Category id.
     * @return \App\Model\Entity\Category
     */
    public function getCached($categoryId)
    {
        $category = false;

        $projectCategories = Cache::read('project-categories-' . CurrentLocation::getProject());

        if ($projectCategories) {
            foreach ($projectCategories as $cat) {
                if ($cat->id == $categoryId) {
                    $category = $cat;
                    break;
                }
            }
        }

        if (!$category) {
            $category = $this->find()
                ->select()
                ->where(['id' => $categoryId])
                ->cache('category-' . $categoryId)
                ->first();
        }

        return $category;
    }
}
