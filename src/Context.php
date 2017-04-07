<?php

namespace Cargo;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * @package Cargo
 */
class Context
{
    /**
     * @var string
     */
    protected static $env;

    /**
     * @var Config
     */
    protected static $config;

    /**
     * @param string $configFilePath
     */
    public function __construct($configFilePath)
    {
        if (!file_exists($configFilePath)) {
            throw new \RuntimeException('The file "'.$configFilePath.'" was not found.');
        }
        if (!is_readable($configFilePath)) {
            throw new \RuntimeException('The file "'.$configFilePath.'" is not readable.');
        }

        try {
            $parser = new Parser();
            $rawConfig = $parser->parse(file_get_contents($configFilePath));

            self::$config = new Config();
            if (!empty($rawConfig['default'])) {
                self::$config->addParamsFromArray($rawConfig['default']);
            }
            if (!empty($rawConfig['environments'][$env])) {
                self::$config->addParamsFromArray($rawConfig['environments'][$env]);
            }
        } catch (ParseException $exception) {
            throw new \RuntimeException('Error while parsing the file "'.$configFilePath.'".');
        }

        if (self::$config->isLoggingEnabled()) {
            self::initLogger();
        }
    }

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param string $env
     * @return $this
     */
    public function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * @return Config
     */
    public static function getConfig()
    {
        return self::$config;
    }
}
