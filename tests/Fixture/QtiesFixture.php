<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QtiesFixture
 */
class QtiesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fb',
                'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                'sort_order' => 1,
                'descript' => 'Under the House',
                'aux_formula' => null,
                'aux_value' => null,
                'qty_formula' => '=8.2*10',
                'qty_value' => 82,
                'created' => '2018-09-29 13:43:48',
                'modified' => '2018-09-29 13:43:48',
            ],
            [
                'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fc',
                'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                'sort_order' => 2,
                'descript' => 'Under the Garage',
                'aux_formula' => null,
                'aux_value' => null,
                'qty_formula' => '=6*5',
                'qty_value' => 30,
                'created' => '2018-09-29 13:43:48',
                'modified' => '2018-09-29 13:43:48',
            ],
            [
                'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fd',
                'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149d',
                'sort_order' => 1,
                'descript' => 'Of 2nd Item',
                'aux_formula' => null,
                'aux_value' => null,
                'qty_formula' => '20',
                'qty_value' => 20,
                'created' => '2018-09-29 13:43:48',
                'modified' => '2018-09-29 13:43:48',
            ],

            [
                'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fe',
                'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149e',
                'sort_order' => 1,
                'descript' => 'Of 1st Item, 1st Section, 2nd Category',
                'aux_formula' => null,
                'aux_value' => null,
                'qty_formula' => '=4.2*5',
                'qty_value' => 21,
                'created' => '2018-09-29 13:43:48',
                'modified' => '2018-09-29 13:43:48',
            ],
        ];
        parent::init();
    }
}
