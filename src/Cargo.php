<?php

namespace Cargo;

use Symfony\Component\Finder\Finder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * The console Application for launching the Cargo command in a standalone instance
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class Cargo extends Application
{
    protected static $context;

    /**
     * @param string $configFilePath
     */
    public function __construct($configFilePath)
    {
        parent::__construct('Cargo', Constants::VERSION);

        $dispatcher = new EventDispatcher();
        $this->setDispatcher($dispatcher);

        $dispatcher->addListener(ConsoleEvents::EXCEPTION, function (ConsoleExceptionEvent $event) {
            $output = $event->getOutput();
            $command = $event->getCommand();
            $output->writeln(
                'An exception has been thrown while running command <comment>'.$command->getName().'</comment>'
            );
            $exitCode = $event->getExitCode();
            $event->setException(new \RuntimeException('Caught exception', $exitCode, $event->getException()));
        });

        $this->loadCommands();
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        if (!self::$context) {
            self::$context = new Context();
        }

        return self::$context;
    }

    /**
     * Configure the Magallanes Application
     *
     * @throws RuntimeException
     */
    public function configure()
    {
        if (!file_exists($this->file) || !is_readable($this->file)) {
            throw new RuntimeException(sprintf('The file "%s" does not exists or is not readable.', $this->file));
        }
        try {
            $parser = new Parser();
            $config = $parser->parse(file_get_contents($this->file));
        } catch (ParseException $exception) {
            throw new RuntimeException(sprintf('Error parsing the file "%s".', $this->file));
        }
        if (array_key_exists('magephp', $config) && is_array($config['magephp'])) {
            $logger = null;
            if (array_key_exists('log_dir', $config['magephp']) && file_exists($config['magephp']['log_dir']) && is_dir($config['magephp']['log_dir'])) {
                $logfile = sprintf('%s/%s.log', $config['magephp']['log_dir'], date('Ymd_His'));
                $config['magephp']['log_file'] = $logfile;
                $logger = new Logger('magephp');
                $logger->pushHandler(new StreamHandler($logfile));
            }
            $this->context->setConfiguration($config['magephp']);
            $this->context->setLogger($logger);
            return;
        }
        throw new RuntimeException(sprintf('The file "%s" does not have a valid Magallanes configuration.', $this->file));
    }

    protected function loadCommands()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/Command')->name('*Command.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $class = preg_match('#^namespace\s+(.+?);$#sm', $file->getContents(), $m) ? $m[1] : null;
            if (class_exists($class) && (new \ReflectionClass($class))->isInstantiable()) {
                $command = new $class();
                if (method_exists($command, 'setContext')) {
                    $command->setContext($this->getContext());
                    $this->add($command);
                }
            }
        }
    }
}
