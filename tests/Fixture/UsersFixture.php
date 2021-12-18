<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'id' => '1d6d0308-9a84-4df2-9254-641be5b8a332',
                'company_id' => '837af879-70fc-49e3-9699-9aca6845fb84',
                'kind' => 'A',
                'name' => 'Administrator',
                'username' => 'admin',
                'passwd' => 'admin123',
                'email' => 'info@pogo.si',
                'reset_key' => null,
                'privileges' => 5,
                'created' => '2018-10-08 06:04:20',
                'modified' => '2018-10-08 06:04:20',
            ],
        ];
        parent::init();
    }
}
