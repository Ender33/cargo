<?php

namespace Cargo;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class Context
 * @package Cargo
 */
class Context
{
    protected static $logger;

    protected static $config;

    public function __construct($env, $configFilePath)
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

        if (self::$config->getParam('log.enabled', false)) {
            $logDir = self::$config->getParam('log.dir');

            if (!is_dir($logDir)) {
                mkdir($logDir, 0750, true);
            }
            if (!is_dir($logDir)) {
                throw new \RuntimeException(
                    'The logs directory "'.$logDir.'" does not exist and could not be created.'
                );
            }
            if (!is_writable($logDir)) {
                throw new \RuntimeException(
                    'The logs directory "'.$logDir.'" is not writable.'
                );
            }

            $logFile = $logDir.DIRECTORY_SEPARATOR.$env.'_'.date('Ymd_His').'.log';
            if (!file_exists($logFile)) {
                touch($logFile);
            }
            if (!file_exists($logFile)) {
                throw new \RuntimeException(
                    'The log file "'.$logFile.'" does not exist and could not be created.'
                );
            }
            if (!is_writable($logFile)) {
                throw new \RuntimeException('The log file "'.$logFile.'" is not writable.');
            }

            self::$logger = new Logger('cargo', array(new StreamHandler($logFile)));

            return;
        }
    }

    /**
     * @return Config
     */
    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * @return Logger
     */
    public static function getLogger()
    {
        return self::$logger;
    }
}
