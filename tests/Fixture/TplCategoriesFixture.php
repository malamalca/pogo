<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TplCategoriesFixture
 */
class TplCategoriesFixture extends TestFixture
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
                'id' => '54bef267-8061-495a-a1d2-a6b1fa01159f',
                'prefix' => '',
                'code' => 'Lorem ipsum dolor sit ame',
                'title' => 'Lorem ipsum dolor sit amet',
                'created' => '2018-10-23 10:50:15',
                'modified' => '2018-10-23 10:50:15',
            ],
        ];
        parent::init();
    }
}
