<?php

namespace SprykerFeature\Zed\Installer\Business\Model;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SprykerEngine\Shared\Kernel\Messenger\MessengerInterface;

abstract class AbstractInstaller extends AbstractLogger implements MessengerInterface
{

    /**
     * @var MessengerInterface
     */
    protected $messenger;

    /**
     * @param MessengerInterface $messenger
     *
     * @return $this
     */
    public function setMessenger(MessengerInterface $messenger)
    {
        $this->messenger = $messenger;

        return $this;
    }

    /**
     * @return void
     */
    abstract public function install();

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->messenger->log($level, $message, $context);
    }

}