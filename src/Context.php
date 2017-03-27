<?php

namespace Cargo;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class Context
 * @package Cargo
 */
class Context
{
    protected $logger;

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
            $mergedConfig = [];
            $rawConfig = $parser->parse(file_get_contents($configFilePath));
            if (!empty($rawConfig['default'])) {
                Config::addParamsFromArray($rawConfig['default']);
            }
            if (!empty($rawConfig['environments'][$env])) {
                Config::addParamsFromArray($rawConfig['environments'][$env]);
            }
            Config::setParams($rawParams);
        } catch (ParseException $exception) {
            throw new \RuntimeException('Error while parsing the file "'.$configFilePath.'".');
        }

        if (array_key_exists('magephp', $config) && is_array($config['magephp'])) {
            $logger = null;
            if (array_key_exists('log_dir', $config['magephp']) && file_exists($config['magephp']['log_dir']) && is_dir($config['magephp']['log_dir'])) {
                $logfile = sprintf('%s/%s.log', $config['magephp']['log_dir'], date('Ymd_His'));
                $config['magephp']['log_file'] = $logfile;

                $logger = new Logger('magephp');
                $logger->pushHandler(new StreamHandler($logfile));
            }

            $this->runtime->setConfiguration($config['magephp']);
            $this->runtime->setLogger($logger);
            return;
        }

        throw new RuntimeException(sprintf('The file "%s" does not have a valid Magallanes configuration.', $this->file));
    }
}
