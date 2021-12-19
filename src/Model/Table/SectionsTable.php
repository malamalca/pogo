<?php
declare(strict_types=1);

namespace App\Model\Table;

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
 * Sections Model
 *
 * @method \App\Model\Entity\Section get(mixed $id, array $options = [])
 * @method \App\Model\Entity\Section newEmptyEntity()
 * @method \App\Model\Entity\Section patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \Cake\ORM\Association\HasMany|\App\Model\Table\ItemsTable $Items
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 */
class SectionsTable extends Table
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

        $this->setTable('sections');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
        $this->hasMany('Items', [
            'foreignKey' => 'section_id',
            'dependent' => true,
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'section_id',
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

        $validator
            ->allowEmptyString('descript');

        $validator
            ->integer('sort_order')
            //->requirePresence('sort_order', 'create')
            ->notEmptyString('sort_order');

        $validator
            ->decimal('total')
            //->requirePresence('total', 'create')
            ->notEmptyString('total');

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
        $rules->add($rules->existsIn(['category_id'], 'Categories'));

        return $rules;
    }

    /**
     * AfterSave Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Section $section Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $section, ArrayObject $options)
    {
        if (empty($options['duplicate'])) {
            if ($section->isNew()) {
                if (empty($section->sort_order)) {
                    $query = $this->find();
                    $order = $query
                        ->select(['max_order' => $query->func()->max('sort_order')])
                        ->where([
                            'category_id' => $section->category_id,
                            'id <>' => $section->id,
                        ])
                        ->enableHydration(false)
                        ->all()
                        ->toArray();

                    $section->sort_order = $order[0]['max_order'] + 1;
                    $this->save($section);
                } else {
                    $this->updateAll(
                        ['sort_order' => new QueryExpression('sort_order + 1')],
                        [
                            'category_id' => $section->category_id,
                            'sort_order >=' => $section->sort_order,
                            'id <>' => $section->id,
                        ]
                    );
                }
            }

            $category = $this->Categories->getCached($section->category_id);
            Cache::delete('project-categories-' . $category->project_id);

            if ($section->isDirty('total')) {
                $query = $this->find();
                $category_total = $query
                    ->select(['category_total' => $query->func()->sum('total')])
                    ->where(['category_id' => $section->category_id])
                    ->enableHydration(false)
                    ->all()
                    ->toArray();
                $category->total = $category_total[0]['category_total'];
                $this->Categories->save($category);
            }
        }
    }

    /**
     * reorder method
     *
     * @param \App\Model\Entity\Section $section Section
     * @param string $category_id Category id.
     * @param int $new_pos New position inside project.
     * @return bool
     */
    public function reorder($section, $category_id, $new_pos)
    {
        if (empty($category_id)) {
            $category_id = $section->category_id;
        }

        /** @var \App\Model\Table\CategoriesTable $CategoriesTable */
        $CategoriesTable = TableRegistry::get('Categories');

        if ($category_id == $section->category_id) {
            $delta = '+'; // moving up
            if ($section->sort_order < $new_pos) {
                $delta = '-'; // moving down
            }

            // update all for new
            $this->updateAll(
                ['sort_order' => new QueryExpression('sort_order ' . $delta . ' 1')],
                [
                    'category_id' => $section->category_id,
                    (new QueryExpression())->between(
                        'sort_order',
                        min($section->sort_order, $new_pos),
                        max($section->sort_order, $new_pos)
                    ),
                ]
            );

            // update sorted item
            $this->updateAll(['category_id' => $category_id, 'sort_order' => $new_pos], ['id' => $section->id]);
        } else {
            // move to another Category
            $this->updateAll(
                ['sort_order' => new QueryExpression('sort_order - 1')],
                [
                    'category_id' => $section->category_id,
                    'sort_order >' => $section->sort_order,
                ]
            );

            // move to another Category
            $this->updateAll(
                ['sort_order' => new QueryExpression('sort_order + 1')],
                [
                    'category_id' => $category_id,
                    'sort_order >=' => $new_pos,
                ]
            );

            // update sorted item
            $this->updateAll(['category_id' => $category_id, 'sort_order' => $new_pos], ['id' => $section->id]);

            // recalc category
            $CategoriesTable->recalc($section->category_id);
            $CategoriesTable->recalc($category_id);
        }

        $category = $CategoriesTable->get($category_id);
        Cache::delete('project-categories-' . $category->project_id);

        return true;
    }

    /**
     * Recalculate
     *
     * @param string $sectionId Section id.
     * @return bool
     */
    public function recalc($sectionId)
    {
        $section = $this->get($sectionId);
        if (!empty($section)) {
            $items = $this->Items->find()->select()->where(['section_id' => $sectionId])->all();

            $sum = 0;
            foreach ($items as $item) {
                $sum += $item->qty * $item->price;
            }
            $section->total = $sum;

            return (bool)$this->save($section);
        }

        return false;
    }

    /**
     * Returns all sections for specified category
     *
     * @param \App\Model\Entity\Category $category Category Entity
     * @return array|object
     */
    public function findForCategory($category)
    {
        $projectCategories = Cache::read('project-categories-' . $category->project_id);

        if ($projectCategories) {
            foreach ($projectCategories as $cat) {
                if ($cat->id == $category->id) {
                    return $cat->sections;
                }
            }
        }

        return $this->find()->select()->where(['category_id' => $category->id])->all();
    }
}
