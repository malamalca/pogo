<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProjectsUsersFixture
 */
class ProjectsUsersFixture extends TestFixture
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
                'id' => 1,
                'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
                'user_id' => '1d6d0308-9a84-4df2-9254-641be5b8a332',
                'role' => 5,
                'archived' => 1,
                'created' => '2018-10-08 06:07:17',
                'modified' => '2018-10-08 06:07:17',
            ],
        ];
        parent::init();
    }
}
