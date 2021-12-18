<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\SectionsController Test Case
 *
 * @uses \App\Controller\SectionsController
 */
class SectionsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Sections',
        'app.Categories',
        'app.Items',
        'app.QtiesTags',
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
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
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

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->login();

        $this->get('/sections/delete/6d413fcd-2812-4a92-b686-02a4810ffce6');
        $this->assertRedirect();
    }

    /**
     * Test reorder method
     *
     * @return void
     */
    public function testReorder(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
