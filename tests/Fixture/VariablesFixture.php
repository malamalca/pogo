<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VariablesFixture
 */
class VariablesFixture extends TestFixture
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
                'id' => 'e2a10086-7389-4bc1-a812-4c3575eb4bbb',
                'project_id' => 'f919eeaf-42c4-4837-897a-dd97038de95c',
                'name' => 'Lorem ipsum dolor sit amet',
                'value' => 1.5,
                'created' => '2018-09-28 11:56:14',
                'modified' => '2018-09-28 11:56:14',
            ],
        ];
        parent::init();
    }
}
