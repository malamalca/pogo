<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Lib\EvalMath;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Qties Model
 *
 * @property \App\Model\Table\ItemsTable $Items
 * @property \Cake\ORM\Association\HasMany $QtiesTags
 * @property \Cake\ORM\Association\BelongsToMany $Variables
 */
class QtiesTable extends Table
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

        $this->setTable('qties');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
        ]);
        $this->hasMany('QtiesTags', [
            'foreignKey' => 'qty_id',
        ]);
        $this->belongsToMany('Variables', [
            'foreignKey' => 'qty_id',
            'targetForeignKey' => 'variable_id',
            'joinTable' => 'qties_variables',
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
            //->requirePresence('sort_order', 'create')
            ->notEmptyString('sort_order');

        $validator
            ->allowEmptyString('descript');

        $validator
            ->allowEmptyString('aux_formula');

        $validator
            ->allowEmptyString('aux_value');

        $validator
            ->allowEmptyString('qty_formula');

        $validator
            ->allowEmptyString('qty_value');

        $validator
            ->allowEmptyString('shapes');

        /*$validator
            ->integer('shapes_no')
            ->requirePresence('shapes_no', 'create')
            ->notEmpty('shapes_no');

        $validator
            ->decimal('shapes_len')
            ->requirePresence('shapes_len', 'create')
            ->notEmpty('shapes_len');

        $validator
            ->decimal('shapes_area')
            ->requirePresence('shapes_area', 'create')
            ->notEmpty('shapes_area');*/

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
        $rules->add($rules->existsIn(['item_id'], 'Items'));

        return $rules;
    }

    /**
     * beforeMarshal Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \ArrayObject $data Data array.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['qty_formula'])) {
            $EvalMath = EvalMath::getInstance();

            if (isset($data['aux_formula'])) {
                $aux_value = $EvalMath->e($data['aux_formula']);
                if ($aux_value !== false) {
                    $EvalMath->setVar('Aux', $aux_value);
                }
            }

            $data['qty_value'] = $EvalMath->evaluate($data['qty_formula']);
            $data['qty_formula'] = $EvalMath->delocalize($data['qty_formula']);

            $EvalMath->unsetVar('Aux');
        }

        if (!empty($data['aux_formula'])) {
            $EvalMath = EvalMath::getInstance();

            $data['aux_value'] = $EvalMath->evaluate($data['aux_formula']);
            $data['aux_formula'] = $EvalMath->delocalize($data['aux_formula']);
        }
    }

    /**
     * AfterSave Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Qty $qty Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $qty, ArrayObject $options)
    {
        // dont do any recalc on duplicate
        if (empty($options['duplicate'])) {
            if ($qty->isDirty('qty_value')) {
                $query = $this->find();
                $item_total = $query
                    ->select(['qty_sum' => $query->func()->sum('qty_value')])
                    ->where(['item_id' => $qty->item_id])
                    ->first()
                    ->toArray();

                $item = $this->Items->get($qty->item_id);
                $item->qty = $item_total['qty_sum'];
                $this->Items->save($item);
            }
        }
    }

    /**
     * AfterDelete Model Event.
     *
     * @param \Cake\Event\Event $event Event Object.
     * @param \App\Model\Entity\Qty $qty Entity Object.
     * @param \ArrayObject $options Options array.
     * @return void
     */
    public function afterDelete(Event $event, EntityInterface $qty, ArrayObject $options)
    {
        $query = $this->find();
        $item_total = $query
            ->select(['qty_sum' => $query->func()->sum('qty_value')])
            ->where(['item_id' => $qty->item_id])
            ->first()
            ->toArray();

        $item = $this->Items->get($qty->item_id);
        $item->qty = $item_total['qty_sum'];
        $this->Items->save($item);
    }

    /**
     * Returns qty sum for specified item
     *
     * @param string $itemId Item id.
     * @return float
     */
    public function sumForItem($itemId)
    {
        $query = $this->find();
        $item_total = $query
            ->select(['qty_sum' => $query->func()->sum('qty_value')])
            ->where(['item_id' => $itemId])
            ->first()
            ->toArray();

        return empty($item_total['qty_sum']) ? 0 : $item_total['qty_sum'];
    }
}
