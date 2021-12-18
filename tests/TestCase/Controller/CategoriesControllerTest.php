<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CategoriesController Test Case
 */
class CategoriesControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Categories',
        'app.Projects',
        'app.QtiesTags',
        'app.Sections',
        'app.ProjectsUsers',
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
    public function testView()
    {
        // without user
        $this->get('/categories/view/e208eb93-1c4e-4bee-87d7-200370217a5e');
        $this->assertRedirect();

        // user login
        $this->login();

        // valid category
        $this->get('/categories/view/e208eb93-1c4e-4bee-87d7-200370217a5e');
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

        $this->get('/categories/edit?project=98a29be9-340b-4ef3-a4e7-86c227920b94');
        $this->assertNoRedirect();
        $this->assertResponseOk();

        $this->enableSecurityToken();
        $this->enableCsrfToken();

        // post
        $this->post('/categories/edit?project=98a29be9-340b-4ef3-a4e7-86c227920b94', [
            'id' => '',
            'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
            'title' => '00000000001',
        ]);
        $this->assertRedirect();

        $category = TableRegistry::get('Categories')->find()->select()->where(['title' => '00000000001'])->first();
        $this->assertNotEmpty($category);
        $this->assertEquals('00000000001', $category->title);
        $this->assertRedirect(['action' => 'view', $category->id]);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        // user login
        $this->login();

        // valid category
        $this->get('/categories/edit/e208eb93-1c4e-4bee-87d7-200370217a5e');
        $this->assertResponseOk();

        $this->enableSecurityToken();
        $this->enableCsrfToken();

        // post
        $this->post('/categories/edit/e208eb93-1c4e-4bee-87d7-200370217a5e', [
            'id' => 'e208eb93-1c4e-4bee-87d7-200370217a5e',
            'project_id' => '98a29be9-340b-4ef3-a4e7-86c227920b94',
            'title' => 'Totally New Name',
        ]);

        $category = TableRegistry::get('Categories')->get('e208eb93-1c4e-4bee-87d7-200370217a5e');
        $this->assertEquals('Totally New Name', $category->title);
        $this->assertRedirect(['action' => 'view', 'e208eb93-1c4e-4bee-87d7-200370217a5e']);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        // user login
        $this->login();

        // valid category
        $this->get('/categories/delete/e208eb93-1c4e-4bee-87d7-200370217a5e');
        $this->assertRedirect(['controller' => 'Projects', 'action' => 'view', '98a29be9-340b-4ef3-a4e7-86c227920b94']);
    }

    /**
     * Test reorder method
     *
     * @return void
     */
    public function testReorder()
    {
        // user login
        $this->login();

        $this->disableErrorHandlerMiddleware();

        // valid category
        $this->get('/categories/reorder/e208eb93-1c4e-4bee-87d7-200370217a5e/2');
        $this->assertRedirect(['controller' => 'Categories', 'action' => 'view', 'e208eb93-1c4e-4bee-87d7-200370217a5e']);

        $category = TableRegistry::get('Categories')->get('e208eb93-1c4e-4bee-87d7-200370217a5e');
        $this->assertEquals(2, $category->sort_order);

        $category = TableRegistry::get('Categories')->get('e208eb93-1c4e-4bee-87d7-200370217a5f');
        $this->assertEquals(1, $category->sort_order);
    }
}
