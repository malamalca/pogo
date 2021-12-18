<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QtiesTagsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QtiesTagsTable Test Case
 */
class QtiesTagsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QtiesTagsTable
     */
    public $QtiesTags;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.QtiesTags',
        'app.Projects',
        'app.Categories',
        'app.Sections',
        'app.Items',
        'app.Qties',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('QtiesTags') ? [] : ['className' => QtiesTagsTable::class];
        $this->QtiesTags = TableRegistry::getTableLocator()->get('QtiesTags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->QtiesTags);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
