<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Lib\EvalMath;
use Cake\ORM\Entity;

/**
 * Qty Entity.
 *
 * @property string $id
 * @property string $item_id
 * @property \App\Model\Entity\Item $item
 * @property int $sort_order
 * @property string $descript
 * @property string|null $aux_formula
 * @property string $aux_value
 * @property string|null $qty_formula
 * @property string $qty_value
 * @property string $shapes
 * @property int $shapes_no
 * @property float $shapes_len
 * @property float $shapes_area
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\QtiesTag[] $qties_tags
 * @property \App\Model\Entity\Variable[] $variables
 */
class Qty extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<array-key, bool>
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    protected $_virtual = ['i18n_aux_formula', 'i18n_qty_formula'];

    /**
     *  Virtual property that converts qty formula to localized version
     *
     * @return bool|float|string|null
     */
    public function _getI18nQtyFormula()    // phpcs:ignore
    {
        if (!isset($this->qty_formula) || is_null($this->qty_formula)) {
            return null;
        }

        $EvalMath = EvalMath::getInstance();

        if (isset($this->aux_value)) {
            $EvalMath->setVar('Aux', (float)$this->aux_value);
        }

        $result = $EvalMath->localize($this->qty_formula);
        $EvalMath->unsetVar('Aux');

        return $result;
    }

    /**
     *  Virtual property that converts aux formula to localized version
     *
     * @return bool|float|null|string
     */
    public function _getI18nAuxFormula()    // phpcs:ignore
    {
        if (!isset($this->aux_formula) || is_null($this->aux_formula)) {
            return null;
        }

        $EvalMath = EvalMath::getInstance();

        return $EvalMath->localize((string)$this->aux_formula);
    }
}
