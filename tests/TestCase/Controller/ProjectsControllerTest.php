<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ProjectsController Test Case
 */
class ProjectsControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Projects',
        'app.Categories',
        'app.Sections',
        'app.QtiesTags',
        'app.Variables',
        'app.ProjectsUsers',
        'app.Users',
    ];

    /**
     * User login
     *
     * @param string $userId User id. Defaults to first (admin) user.
     * @return void
     */
    private function login($userId = '1d6d0308-9a84-4df2-9254-641be5b8a332')
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get($userId);
        $this->session(['Auth' => $user]);
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->login();

        $this->get('/projects/index');
        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        // without user
        $this->get('/projects/view/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect();

        // user login
        $this->login();

        // invalid project
        $this->disableErrorHandlerMiddleware();
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);
        $this->get('/projects/view/98a29be9-340b-4ef3-eeee-86c227920b94');

        // valid project
        $this->get('/projects/view/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertResponseOk();
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->login();

        //$this->get('/projects/add');
        //$this->assertResponseOk();

        $this->disableErrorHandlerMiddleware();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // post
        $this->post('/projects/edit', [
            'id' => '',
            'no' => '00000000001',
            'title' => 'Hello World',
            'subtitle' => null,
            'descript' => null,
            'dat_place' => 'Ljubljana, april 2018',
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
        ]);

        $project = TableRegistry::getTableLocator()->get('Projects')->find()->select()->where(['no' => '00000000001'])->first();

        $this->assertNotEmpty($project);
        $this->assertEquals('Hello World', $project->title);
        $this->assertRedirect(['action' => 'view', $project->id]);

        $projectUsers = TableRegistry::getTableLocator()->get('ProjectsUsers')->find()->select()->where([
            'user_id' => '1d6d0308-9a84-4df2-9254-641be5b8a332',
            'project_id' => $project->id,
        ])->first();

        $this->assertNotEmpty($projectUsers);
        $this->assertEquals(5, $projectUsers->role);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        // without user
        $this->get('/projects/edit/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect();

        // user login
        $this->login();

        // invalid project
        $this->disableErrorHandlerMiddleware();
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);

        $this->get('/projects/edit/98a29be9-340b-4ef3-eeee-86c227920b94');

        // valid project
        $this->get('/projects/edit/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertResponseOk();

        // post
        $this->post('/projects/edit/98a29be9-340b-4ef3-a4e7-86c227920b94', [
            'id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
            'no' => '2018-01',
            'title' => 'Hello World',
            'subtitle' => null,
            'descript' => null,
            'dat_place' => 'Ljubljana, april 2018',
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
        ]);

        $project = TableRegistry::getTableLocator()->get('Projects')->get('98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertEquals('Hello World', $project->title);
        $this->assertRedirect(['action' => 'view', '98a29be9-340b-4ef3-a4e7-86c227920b94']);
    }

    /**
     * Test notes method
     *
     * @return void
     */
    public function testNotes()
    {
        // without user
        $this->get('/projects/notes/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect();

        // user login
        $this->login();

        // invalid project
        $this->disableErrorHandlerMiddleware();
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);

        $this->get('/projects/notes/98a29be9-340b-4ef3-eeee-86c227920b94');

        // valid project
        $this->get('/projects/notes/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertResponseOk();

        // post
        $this->post('/projects/notes/98a29be9-340b-4ef3-a4e7-86c227920b94', [
            'id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
            'notes' => 'A new note',
        ]);

        $project = TableRegistry::getTableLocator()->get('Projects')->get('98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertEquals('A new note', $project->notes);
        $this->assertResponseOk();
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        // without user
        $this->get('/projects/delete/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect();

        // user login
        $this->login();

        // invalid project
        $this->disableErrorHandlerMiddleware();
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);

        $this->get('/projects/delete/98a29be9-340b-4ef3-eeee-86c227920b94');

        // valid project
        $this->get('/projects/delete/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect(['controller' => 'Projects', 'action' => 'index']);

        // check ProjectsUsers cleanup
        $projectUsers = TableRegistry::getTableLocator()->get('ProjectsUsers')->find()->select()->where([
            'user_id' => '1d6d0308-9a84-4df2-9254-641be5b8a332',
            'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
        ])->first();
        $this->assertEmpty($projectUsers);
    }

    /**
     * Test export method
     *
     * @return void
     */
    public function testExport()
    {
        // without user
        $this->get('/projects/export/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect();

        // user login
        $this->login();

        // invalid project
        $this->disableErrorHandlerMiddleware();
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);

        $this->get('/projects/export/98a29be9-340b-4ef3-eeee-86c227920b94');

        // valid project
        $this->get('/projects/delete/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertRedirect(['controller' => 'Projects', 'action' => 'index']);
    }
}
