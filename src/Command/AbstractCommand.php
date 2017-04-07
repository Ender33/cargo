<?php

namespace Cargo\Command;

use Cargo\Context;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Logger
     */
    protected static $logger;



    protected function initLogger()
    {
        $logDir = $this->context->getConfig()->getParam('log.dir');

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

        $logFile = $logDir.DIRECTORY_SEPARATOR.$this->getEnv().'_'.date('Ymd_His').'.log';
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
    }
}
