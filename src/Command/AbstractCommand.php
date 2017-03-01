<?php

namespace Cargo\Command;

use Cargo\MageApplication;
use Cargo\Utils;
use Cargo\Context;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var Context Current Context instance
     */
    protected $context;

    /**
     * @param Context $context
     * @return AbstractCommand
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the Human friendly Stage name
     *
     * @return string
     */
    protected function getStageName()
    {
        $utils = new Utils();
        return $utils->getStageName($this->context->getStage());
    }
    /**
     * Requires the configuration to be loaded
     */
    protected function requireConfig()
    {
        $app = $this->getApplication();
        if ($app instanceof MageApplication) {
            $app->configure();
        }
    }
}
