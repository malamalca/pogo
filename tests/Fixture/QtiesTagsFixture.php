<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QtiesTagsFixture
 */
class QtiesTagsFixture extends TestFixture
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
                'id' => '50f30848-cd7d-449d-a1cb-5db8ffafd0eb',
                'project_id' => 'eb9e35ac-9855-407e-84d9-1d39f774cd37',
                'category_id' => '6d6f4f4a-bdde-4f28-add0-7e743e10280b',
                'section_id' => '5231e558-2f70-4d45-a3eb-4aa74dafa033',
                'item_id' => 'bcf11bb5-4672-490a-bb5c-c5de4cf08f3b',
                'qty_id' => '6153bb50-0781-45ea-866e-c997fc72cabc',
                'tag' => 'Lorem ipsum dolor sit amet',
                'created' => '2018-09-27 16:26:21',
                'modified' => '2018-09-27 16:26:21',
            ],
        ];
        parent::init();
    }
}
