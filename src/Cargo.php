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

        self::$context = new Context({env} $configFilePath);

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
                    $command->setContext(self::$context);
                    $this->add($command);
                }
            }
        }
    }
}
