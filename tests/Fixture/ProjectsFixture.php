<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProjectsFixture
 */
class ProjectsFixture extends TestFixture
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
                'id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
                'no' => '2018-01',
                'title' => 'First Test Project',
                'subtitle' => null,
                'dat_place' => 'Ljubljana, april 2018',
                'descript' => null,
                'active' => 1,

                'investor_title' => 'Builder ltd',
                'investor_address' => 'Over the Rainbow 12',
                'investor_zip' => '1000',
                'investor_post' => 'Ljubljana',
                'creator_title' => 'ARHIM d.o.o.',
                'creator_address' => 'Slakova ulica 36',
                'creator_zip' => '8210',
                'creator_post' => 'Trebnje',
                'creator_person' => 'Miha Nahtigal',

                'notes' => null,
                'created' => '2018-09-29 13:38:03',
                'modified' => '2018-09-29 13:38:03',
            ],
        ];
        parent::init();
    }
}
