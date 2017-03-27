<?php

namespace Cargo\Command;

use Cargo\Context;
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
     * @param Context $context
     * @return AbstractCommand
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }
}
