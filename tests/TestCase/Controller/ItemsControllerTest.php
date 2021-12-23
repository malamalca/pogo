<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Lib\EvalMath;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ItemsController Test Case
 */
class ItemsControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Items',
        'app.Sections',
        'app.Qties',
        'app.QtiesTags',
        'app.Categories',
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
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        I18n::setLocale('en_US');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        // user login
        $this->login();

        // valid item
        $this->get('/items/view/b4b94542-d140-4efc-afe0-53a21bc0149c');
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

        $sectionBefore = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);

        $this->enableSecurityToken();
        $this->enableCsrfToken();

        // post
        $this->post('/items/edit', [
            'id' => '',
            'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
            'sort_order' => '3',
            'descript' => 'A new item from tests',
            'unit' => 'm^2',
            'qty' => '100.5',
            'price' => '2.5',
            'qties' => [
                0 => [
                    'id' => '',
                    'item_id' => '',
                    'sort_order' => 3,
                    'descript' => 'Under the Pool',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=22.5*2',
                    'qty_value' => 45,
                ],
                1 => [
                    'id' => '',
                    'item_id' => '',
                    'sort_order' => 4,
                    'descript' => 'Under the Deck',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=55.5',
                    'qty_value' => 55.5,
                ],
            ],
        ]);

        $this->assertRedirect(['controller' => 'Sections', 'action' => 'view', '6d413fcd-2812-4a92-b686-02a4810ffce6']);

        $item = TableRegistry::getTableLocator()->get('Items')->find()->select()->where(['descript' => 'A new item from tests'])->first();
        $this->assertNotEmpty($item);
        $this->assertEquals(100.5, $item->qty);

        $sectionAfter = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);
        $this->assertNotEmpty($sectionAfter);
        $this->assertEquals($sectionBefore->total, $sectionAfter->total - 100.5 * 2.5);
        $this->assertEquals(count($sectionBefore->items), count($sectionAfter->items) - 1);
    }

    /**
     * Test add sl_SI method
     *
     * @return void
     */
    public function testAddSi()
    {
        EvalMath::getInstance()->destroy();

        I18n::setLocale('sl_SI');

        $this->login();

        $sectionBefore = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);

        $this->enableSecurityToken();
        $this->enableCsrfToken();

        // post
        $this->post('/items/edit', [
            'id' => '',
            'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
            'sort_order' => '3',
            'descript' => 'A new item from tests',
            'unit' => 'm^2',
            'qty' => '100,5',
            'price' => '2,5',
            'qties' => [
                0 => [
                    'id' => '',
                    'item_id' => '',
                    'sort_order' => 3,
                    'descript' => 'Under the Pool',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=22,5*2',
                    'qty_value' => 45,
                ],
                1 => [
                    'id' => '',
                    'item_id' => '',
                    'sort_order' => 4,
                    'descript' => 'Under the Deck',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=55,5',
                    'qty_value' => 55.5,
                ],
            ],
        ]);

        $this->assertRedirect(['controller' => 'Sections', 'action' => 'view', '6d413fcd-2812-4a92-b686-02a4810ffce6']);

        $item = TableRegistry::getTableLocator()->get('Items')->find()->select()->where(['descript' => 'A new item from tests'])->first();
        $this->assertNotEmpty($item);
        $this->assertEquals(100.5, $item->qty);

        $sectionAfter = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);
        $this->assertNotEmpty($sectionAfter);
        $this->assertEquals($sectionBefore->total, $sectionAfter->total - 100.5 * 2.5);
        $this->assertEquals(count($sectionBefore->items), count($sectionAfter->items) - 1);

        I18n::setLocale('en_US');
        EvalMath::getInstance()->destroy();
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->login();

        $sectionBefore = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);

        $this->enableSecurityToken();
        $this->enableCsrfToken();

        // post unchanged
        $this->post('/items/edit/b4b94542-d140-4efc-afe0-53a21bc0149c', [
            'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
            'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
            'sort_order' => 1,
            'descript' => 'Ground Works (1st Section)',
            'unit' => 'm^2',
            'qty' => 112,
            'price' => 2,
            'qties' => [
                0 => [
                    'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fb',
                    'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                    'sort_order' => 1,
                    'descript' => 'Under the House',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=8.2*10',
                    'qty_value' => 82,
                ],
                1 => [
                    'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fc',
                    'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                    'sort_order' => 2,
                    'descript' => 'Under the Garage',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=6*5',
                    'qty_value' => 30,
                ],
            ],
        ]);

        $this->assertRedirect(['controller' => 'Sections', 'action' => 'view', '6d413fcd-2812-4a92-b686-02a4810ffce6']);

        $sectionAfter = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);
        $this->assertNotEmpty($sectionAfter);
        $this->assertEquals(count($sectionBefore->items), count($sectionAfter->items));
        $this->assertEquals($sectionBefore->total, $sectionAfter->total);

        // post with edit
        $this->post('/items/edit/b4b94542-d140-4efc-afe0-53a21bc0149c', [
            'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
            'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
            'sort_order' => 1,
            'descript' => 'Ground Works (1st Section)',
            'unit' => 'm^3',
            'qty' => 55,
            'price' => 2,
            'qties' => [
                0 => [
                    'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fb',
                    'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                    'sort_order' => 1,
                    'descript' => 'Under the House',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=8*5',
                    'qty_value' => 40,
                ],
                1 => [
                    'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fc',
                    'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                    'sort_order' => 2,
                    'descript' => 'Under the Garage',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=3*5',
                    'qty_value' => 15,
                ],
            ],
        ]);

        $this->assertRedirect(['controller' => 'Sections', 'action' => 'view', '6d413fcd-2812-4a92-b686-02a4810ffce6']);

        $item = TableRegistry::getTableLocator()->get('Items')->find()->select()->where(['id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c'])->first();
        $this->assertNotEmpty($item);
        $this->assertEquals('m^3', $item->unit);

        $sectionAfter = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);
        $this->assertNotEmpty($sectionAfter);
        $this->assertEquals(count($sectionBefore->items), count($sectionAfter->items));
        $this->assertEquals(55 * 2 + 20 * 3, $sectionAfter->total);

        // post with delete
        $this->post('/items/edit/b4b94542-d140-4efc-afe0-53a21bc0149c', [
            'id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
            'section_id' => '6d413fcd-2812-4a92-b686-02a4810ffce6',
            'sort_order' => 1,
            'descript' => 'Ground Works (1st Section)',
            'unit' => 'm^2',
            'qty' => 83,
            'price' => 2,
            'qties' => [
                0 => [
                    'id' => 'cbb49739-f266-4662-8d45-0e9cb730c8fb',
                    'item_id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c',
                    'sort_order' => 1,
                    'descript' => 'Under the House',
                    'aux_formula' => null,
                    'aux_value' => null,
                    'qty_formula' => '=8.3*10',
                    'qty_value' => 83,
                ],
            ],
            'qties_to_delete' => [0 => 'cbb49739-f266-4662-8d45-0e9cb730c8fc'],
        ]);

        $this->assertRedirect(['controller' => 'Sections', 'action' => 'view', '6d413fcd-2812-4a92-b686-02a4810ffce6']);

        $item = TableRegistry::getTableLocator()->get('Items')->find()->select()->contain(['Qties'])->where(['id' => 'b4b94542-d140-4efc-afe0-53a21bc0149c'])->first();
        $this->assertNotEmpty($item);
        $this->assertEquals(1, count($item->qties));
        $this->assertEquals(83, $item->qty);

        $sectionAfter = TableRegistry::getTableLocator()->get('Sections')->get('6d413fcd-2812-4a92-b686-02a4810ffce6', ['contain' => 'Items']);
        $this->assertNotEmpty($sectionAfter);
        $this->assertEquals(count($sectionBefore->items), count($sectionAfter->items));
        $this->assertEquals(83 * 2 + 20 * 3, $sectionAfter->total);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->login();

        $this->get('/items/delete/b4b94542-d140-4efc-afe0-53a21bc0149c');
        $this->assertRedirect();
    }

    /**
     * Test reorder method
     *
     * @return void
     */
    /*public function testReorder()
    {
        //$this->markTestIncomplete('Not implemented yet.');
    }*/
}
