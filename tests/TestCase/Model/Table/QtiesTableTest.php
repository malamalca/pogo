<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QtiesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QtiesTable Test Case
 */
class QtiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QtiesTable
     */
    public $Qties;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Qties',
        'app.Items',
        'app.QtiesTags',
        'app.Variables',
        'app.Sections',
        'app.Categories',
        'app.Projects',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Qties') ? [] : ['className' => QtiesTable::class];
        $this->Qties = TableRegistry::getTableLocator()->get('Qties', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Qties);

        parent::tearDown();
    }

    public function testCascade()
    {
        $qty = $this->Qties->get('cbb49739-f266-4662-8d45-0e9cb730c8fb');

        $qty->qty_value = 10;
        $this->Qties->save($qty);

        $item = TableRegistry::get('Items')->get($qty->item_id);
        $this->assertEquals(40, $item->qty);

        $section = TableRegistry::get('Sections')->get($item->section_id);
        $this->assertEquals(40 * 2 + 20 * 3, $section->total);

        $category = TableRegistry::get('Categories')->get($section->category_id);
        $this->assertEquals((40 * 2 + 20 * 3) + 84, $category->total);
    }
}
