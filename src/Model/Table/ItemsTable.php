<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Items Model
 *
 * @method \App\Model\Entity\Item newEmptyEntity()
 * @method \App\Model\Entity\Item get(mixed $id, array $options = [])
 * @method \App\Model\Entity\Item patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @property \App\Model\Table\SectionsTable $Sections
 * @property \Cake\ORM\Association\HasMany $Fields
 * @property \App\Model\Table\QtiesTable $Qties
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 */
class ItemsTable extends Table
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

        $this->setTable('items');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Sections', [
            'foreignKey' => 'section_id',
        ]);
        $this->hasMany('Fields', [
            'foreignKey' => 'item_id',
        ]);
        $this->hasMany('Qties', [
            'foreignKey' => 'item_id',
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'item_id',
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
            ->integer('sort_order')
            ->allowEmptyString('sort_order');

        $validator
            ->notEmptyString('descript');

        $validator
            ->allowEmptyString('unit');

        $validator
            ->decimal('qty')
            ->allowEmptyString('qty');

        $validator
            ->decimal('price')
            ->allowEmptyString('price');

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
        $rules->add($rules->existsIn(['section_id'], 'Sections'));

        return $rules;
    }

    /**
     * beforeSave Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Item $item Entity Object.
     * @param \ArrayObject $options Options array.
     * @return bool
     */
    public function beforeSave(Event $event, EntityInterface $item, ArrayObject $options)
    {
        if (!empty($item->qties)) {
            $qty_total = 0;
            foreach ($item->qties as $qty) {
                $qty_total += (float)$qty->qty_value;
            }
            $item->qty = $qty_total;
        }

        return true;
    }

    /**
     * AfterSave Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Item $item Entity Object.
     * @param \ArrayObject $options Options array.
     * @return bool
     */
    public function afterSave(Event $event, EntityInterface $item, ArrayObject $options)
    {
        if (empty($options['duplicate'])) {
            // dont do any recalc on duplicate
            if ($item->isNew()) {
                if (empty($item->sort_order)) {
                    $query = $this->find();
                    $order = $query
                        ->select(['max_order' => $query->func()->max('sort_order')])
                        ->where(['section_id' => $item->section_id])
                        ->enableHydration(false)
                        ->all()
                        ->toArray();

                    $item->sort_order = $order[0]['max_order'] + 1;
                    $this->save($item);
                } else {
                    $this->updateAll(
                        ['sort_order' => new QueryExpression('sort_order + 1')],
                        [
                            'section_id' => $item->section_id,
                            'sort_order >=' => $item->sort_order,
                            'id <>' => $item->id,
                        ]
                    );
                }
            }

            if ($item->isDirty('qty')) {
                $query = $this->find();
                $section_total = $query
                    ->select(['sect_total' => $query->func()->sum('qty*price')])
                    ->where(['section_id' => $item->section_id])
                    ->enableHydration(false)
                    ->all()
                    ->toArray();

                $section = $this->Sections->get($item->section_id);
                $section->total = $section_total[0]['sect_total'];
                $this->Sections->save($section);
            }
        }

        return true;
    }

    /**
     * AfterDelete Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Item $item Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterDelete(Event $event, EntityInterface $item, ArrayObject $options)
    {
        $this->updateAll(
            ['sort_order' => new QueryExpression('sort_order - 1')],
            ['section_id' => $item->section_id, 'sort_order >' => $item->sort_order]
        );
    }

    /**
     * Recalculate qties
     *
     * @param string $itemId Item id.
     * @return bool
     */
    public function recalc($itemId)
    {
        $item = $this->get($itemId);
        if (!empty($item)) {
            $sum = 0;

            $qties = $this->Qties->find()->select()->where(['item_id' => $itemId])->all();

            foreach ($qties as $qty) {
                $sum += $qty->qty_value;
            }
            $item->qty = $sum;

            return (bool)$this->save($item);
        }

        return false;
    }

    /**
     * Reorder items
     *
     * @param \App\Model\Entity\Item $item Item
     * @param int $new_pos New position inside section
     * @return bool
     */
    public function reorder($item, $new_pos)
    {
        $delta = '+'; // moving up
        if ($item->sort_order < $new_pos) {
            $delta = '-'; // moving down
        }

        // update all for new
        $this->updateAll(
            ['Items.sort_order' => new QueryExpression('Items.sort_order ' . $delta . ' 1')],
            [
                'Items.section_id' => $item->section_id,
                function (QueryExpression $exp) use ($item, $new_pos) {
                    if ($item->sort_order < $new_pos) {
                        return $exp->between('Items.sort_order', $item->sort_order, $new_pos);
                    } else {
                        return $exp->between('Items.sort_order', $new_pos, $item->sort_order);
                    }
                },
            ]
        );

        // update sorted item
        $this->updateAll(
            ['Items.sort_order' => $new_pos],
            ['Items.id' => $item->id]
        );

        return true;
    }
}
