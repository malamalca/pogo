<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ProjectsUsersController Test Case
 *
 * @uses \App\Controller\ProjectsUsersController
 */
class ProjectsUsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.ProjectsUsers',
        'app.Projects',
        'app.Users',
    ];

    /**
     * Helper function for quick user login
     *
     * @param string $userId User id
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
    public function testIndex(): void
    {
        $this->login();
        $this->get('/ProjectsUsers/index/98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertResponseOk();
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
