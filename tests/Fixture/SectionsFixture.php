<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SectionsFixture
 */
class SectionsFixture extends TestFixture
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
                'id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
                'category_id' => 'e208eb93-1c4e-4bee-87d7-200370217a5e',
                'title' => 'First Section of First Category',
                'descript' => null,
                'sort_order' => 1,
                'total' => 284,
                'created' => '2018-09-29 13:37:55',
                'modified' => '2018-09-29 13:37:55',
            ],
            [
                'id' => '6d413fcd-2812-4a92-b686-02a4810ffce7',
                'category_id' => 'e208eb93-1c4e-4bee-87d7-200370217a5e',
                'title' => 'Second Section of First Category',
                'descript' => null,
                'sort_order' => 2,
                'total' => 84,
                'created' => '2018-09-29 13:37:55',
                'modified' => '2018-09-29 13:37:55',
            ],
            [
                'id' => '6d413fcd-2812-4a92-b686-02a4810ffce8',
                'category_id' => 'e208eb93-1c4e-4bee-87d7-200370217a5f',
                'title' => 'First Section of Second Category',
                'descript' => null,
                'sort_order' => 1,
                'total' => 0,
                'created' => '2018-09-29 13:37:55',
                'modified' => '2018-09-29 13:37:55',
            ],
        ];
        parent::init();
    }
}
