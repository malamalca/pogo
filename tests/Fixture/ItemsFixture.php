<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ItemsFixture
 */
class ItemsFixture extends TestFixture
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
                'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
                'sort_order' => 1,
                'descript' => 'Ground Works (1st Section)',
                'unit' => 'm^2',
                'qty' => 112,
                'price' => 2,
                'created' => '2018-09-29 13:37:48',
                'modified' => '2018-09-29 13:37:48',
            ],
            [
                'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149d',
                'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
                'sort_order' => 2,
                'descript' => 'Floor Works (1st Section)',
                'unit' => 'm^2',
                'qty' => 20,
                'price' => 3,
                'created' => '2018-09-29 13:37:48',
                'modified' => '2018-09-29 13:37:48',
            ],

            [
                'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149e',
                'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce7',
                'sort_order' => 1,
                'descript' => 'Other Works (2nd section)',
                'unit' => 'm^2',
                'qty' => 21,
                'price' => 4,
                'created' => '2018-09-29 13:37:48',
                'modified' => '2018-09-29 13:37:48',
            ],
        ];
        parent::init();
    }
}
