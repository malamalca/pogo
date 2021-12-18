<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CategoriesFixture
 */
class CategoriesFixture extends TestFixture
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
                'id' => 'e208eb93-1c4e-4bee-87d7-200370217a5e',
                'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
                'title' => 'First Category',
                'sort_order' => 1,
                'total' => 368,
                'created' => '2018-09-29 13:37:59',
                'modified' => '2018-09-29 13:37:59',
            ],
            [
                'id' => 'e208eb93-1c4e-4bee-87d7-200370217a5f',
                'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
                'title' => 'Second Category',
                'sort_order' => 2,
                'total' => 0,
                'created' => '2018-09-29 13:37:59',
                'modified' => '2018-09-29 13:37:59',
            ],
        ];
        parent::init();
    }
}
