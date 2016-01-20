<?php
 // Created a Do-Nothing FirePHP class to efficiently, do nothing. -Craig
class FirePHP {
    protected static $instance = null;

    public static function getInstance($AutoCreate = false)
    {
        if ($AutoCreate===true && !self::$instance) {
            self::init();
        }
        return self::$instance;
    }
    public static function init()
    {
        return self::setInstance(new self());
    }
    public static function setInstance($instance)
    {
        return self::$instance = $instance;
    }
    public function log($Object, $Label = null, $Options = array())
    {
        return;
    } 
    public function info($Object, $Label = null, $Options = array())
    {
        return;
    } 

    public function warn($Object, $Label = null, $Options = array())
    {
        return;
    } 
    public function error($Object, $Label = null, $Options = array())
    {
        return;
    } 
    public function dump($Key, $Variable, $Options = array())
    {
        return;
    }
    public function trace($Label)
    {
        return;
    } 
    public function table($Label, $Table, $Options = array())
    {
        return;
    }
}
