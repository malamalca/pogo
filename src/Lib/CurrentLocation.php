<?php
declare(strict_types=1);

namespace App\Lib;

/*
 * Current location class is singleton that stores current project->group->set_exception_handler
 */

class CurrentLocation
{
    private static $instance = null;

    /**
     * @var string|null $projectId
     */
    protected $projectId = null;
    protected $categoryId = null;
    protected $sectionId = null;

    /**
     * Initialization hook method.
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Singletons should not be cloneable.
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * Singletons should not be restorable from strings.
     *
     * @return void
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }

    /**
     * Set project, category and section
     *
     * @param string|null $projectId Project id.
     * @param string|null $categoryId Category id.
     * @param string|null $sectionId Project id.
     * @return void
     */
    public static function set($projectId = null, $categoryId = null, $sectionId = null)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->projectId = $projectId;
        static::$instance->categoryId = $categoryId;
        static::$instance->sectionId = $sectionId;
    }

    /**
     * Set project
     *
     * @param string|null $projectId Project id.
     * @return void
     */
    public static function setProject($projectId = null)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->projectId = $projectId;
    }

    /**
     * Get project
     *
     * @return null|string
     */
    public static function getProject()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->projectId;
    }

    /**
     * Set category
     *
     * @param string $categoryId Category id.
     * @return void
     */
    public static function setCategory($categoryId)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->categoryId = $categoryId;
    }

    /**
     * Get category
     *
     * @return null|string
     */
    public static function getCategory()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->categoryId;
    }

    /**
     * Set section
     *
     * @param string $sectionId Section id.
     * @return void
     */
    public static function setSection($sectionId)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->sectionId = $sectionId;
    }

    /**
     * Get category
     *
     * @return null|string
     */
    public static function getSection()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->sectionId;
    }
}
