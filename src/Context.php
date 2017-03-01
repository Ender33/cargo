<?php

namespace Cargo;

/**
 * Class Context
 * @package Cargo
 */
class Context
{
    protected static $config;

    public function __construct($configFilePath)
    {

    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return self::$config;
    }
}
