<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CompaniesFixture
 */
class CompaniesFixture extends TestFixture
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
                'id' => '837af879-70fc-49e3-9699-9aca6845fb84',
                'title' => 'ARHIM d.o.o.',
                'address' => 'Slakova ulica 36',
                'zip' => '8210',
                'town' => 'Trebnje',
                'phone' => '041 891 824',
                'email' => 'info@arhim.si',
                'created' => '2018-09-29 16:55:42',
                'modified' => '2018-09-29 16:55:42',
            ],
        ];
        parent::init();
    }
}
