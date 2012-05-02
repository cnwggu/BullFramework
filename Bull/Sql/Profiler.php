<?php
/**
 * 
 * This file is part of the Bull Project for PHP.
 * 
 */
use \PDOStatement;

/**
 * 
 * Retains query profiles.
 * 
 * @package Bull.Sql
 * 
 */
class Bull_Sql_Profiler implements Bull_Sql_ProfilerInterface
{
    /**
     * 
     * Is the profiler active?
     * 
     * @var bool
     * 
     */
    protected $active = false;
    
    /**
     * 
     * Retained profiles.
     * 
     * @var array
     * 
     */
    protected $profiles = array();
    
    /**
     * 
     * Turns the profiler on and off.
     * 
     * @param bool $active True to turn on, false to turn off.
     * 
     * @return void
     * 
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }
    
    /**
     * 
     * Is the profiler active?
     * 
     * @return bool
     * 
     */
    public function isActive()
    {
        return (bool) $this->active;
    }
    
    /**
     * 
     * Executes a PDOStatment and profiles it.
     * 
     * @param PDOStatement $stmt The PDOStatement to execute and profile.
     * 
     * @param array $data The data that was bound into the statement.
     * 
     * @return mixed
     * 
     */
    public function exec(PDOStatement $stmt, array $data = array())
    {
        if (! $this->isActive()) {
            return $stmt->execute();
        }
        
        $before = microtime(true);
        $result = $stmt->execute();
        $after = microtime(true);
        $this->addProfile($stmt->queryString, $after - $before, $data);
        return $result;
    }
    
    /**
     * 
     * Calls a user function and profile it.
     * 
     * @param callable $func The user function to call.
     * 
     * @param array $data The data that was used by the function.
     * 
     * @return mixed
     * 
     */
    public function call($func, $text, array $data = array())
    {
        if (! $this->isActive()) {
            return call_user_func($func);
        }
        
        $before = microtime(true);
        $result = call_user_func($func);
        $after  = microtime(true);
        $this->addProfile($text, $after - $before, $data);
        return $result;
    }
    
    /**
     * 
     * Adds a profile to the profiler.
     * 
     * @param string $text The text (typically an SQL query) being profiled.
     * 
     * @param float $time The elapsed time in seconds.
     * 
     * @param array $data The data that was used.
     * 
     * @return mixed
     * 
     */
    public function addProfile($text, $time, array $data = array())
    {
        $this->profiles[] = (object) array(
            'text' => $text,
            'time' => $time,
            'data' => $data,
            'trace' => debug_backtrace()
        );
    }
    
    /**
     * 
     * Returns all the profiles.
     * 
     * @return array
     * 
     */
    public function getProfiles()
    {
        return $this->profiles;
    }
}