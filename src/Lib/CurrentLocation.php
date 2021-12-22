<?php
declare(strict_types=1);

namespace App\Lib;

/*
 * Current location class is singleton that stores current project->group->set_exception_handler
 */

class CurrentLocation
{
    /**
     * @var \App\Lib\CurrentLocation|null $instance
     */
    private static $instance = null;

    /**
     * @var \App\Model\Entity\Project|null $project
     */
    protected $project = null;

    /**
     * @var \App\Model\Entity\Category|null $category
     */
    protected $category = null;

    /**
     * @var \App\Model\Entity\Section|null $section
     */
    protected $section = null;

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
     * @param \App\Model\Entity\Project|null $project Project.
     * @param \App\Model\Entity\Category|null $category Category.
     * @param \App\Model\Entity\Section|null $section Section.
     * @return void
     */
    public static function set($project = null, $category = null, $section = null)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->project = $project;
        static::$instance->category = $category;
        static::$instance->section = $section;
    }

    /**
     * Set project
     *
     * @param \App\Model\Entity\Project|null $project Project.
     * @return void
     */
    public static function setProject($project = null)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->project = $project;
    }

    /**
     * Get project
     *
     * @return \App\Model\Entity\Project|null
     */
    public static function getProject()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->project;
    }

    /**
     * Set category
     *
     * @param \App\Model\Entity\Category $category Category.
     * @return void
     */
    public static function setCategory($category)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->category = $category;
    }

    /**
     * Get category
     *
     * @return \App\Model\Entity\Category|null
     */
    public static function getCategory()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->category;
    }

    /**
     * Set section
     *
     * @param \App\Model\Entity\Section $section Section.
     * @return void
     */
    public static function setSection($section)
    {
        if (static::$instance === null) {
            static::$instance = new CurrentLocation();
        }
        static::$instance->section = $section;
    }

    /**
     * Get category
     *
     * @return \App\Model\Entity\Section|null
     */
    public static function getSection()
    {
        if (static::$instance === null) {
            return null;
        }

        return static::$instance->section;
    }
}
