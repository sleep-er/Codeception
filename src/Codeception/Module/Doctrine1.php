<?php
namespace Codeception\Module;

/**
 * Performs DB operations with Doctrine ORM 1.x
 *
 * Uses active Doctrine connection. If none can be found will fail.
 *
 * This module cleans all cached entities before each test.
 *
 */

class Doctrine1 extends \Codeception\Module
{
    public function _initialize() {
        $this->dbh = \Doctrine_Manager::connection()->getDbh();
    }
    
    public function _after(\Codeception\TestCase $test)
    {
        $this->tables = \Doctrine_Manager::connection()->getTables();
        foreach ($this->tables as $table) {
            foreach ($table->getRepository() as $record) {
                $record->clearRelated();
            }
            $table->getRepository()->evictAll();
            $table->clear();
        }
    }

    protected function proceedSeeInDatabase($model, $values = array())
    {
        $query = \Doctrine_Core::getTable($model)->createQuery();
        $string = array();
        foreach ($values as $key => $value) {
            $query->addWhere("$key = ?", $value);
            $string[] = "$key = '$value'";
        }
        return array('True', ($query->count() > 0), "$model with " . implode(', ', $string));
    }

    /**
     * Checks table contains row with specified values
     * Provide Doctrine model name can be passed to addWhere DQL
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInTable('User', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     *
     * @param $model
     * @param array $values
     */
    public function seeInTable($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assert($res);
    }


    /**
     * Checks table doesn't contain row with specified values
     * Provide Doctrine model name and criteria that can be passed to addWhere DQL
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->dontSeeInTable('User', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     *
     * @param $model
     * @param array $values
     */
    public function dontSeeInTable($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assertNot($res);
    }

}