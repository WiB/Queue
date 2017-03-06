<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Queue\Business;

use Spryker\Client\Queue\QueueClientInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Queue\Business\Model\Task\Task;
use Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorInterface;
use Spryker\Zed\Queue\QueueConfig;
use Spryker\Zed\Queue\QueueDependencyProvider;

/**
 * @method QueueConfig getConfig()
 */
class QueueBusinessFactory extends AbstractBusinessFactory
{

    /**
     * @return Task
     */
    public function createTask()
    {
        return new Task(
            $this->getQueueClient(),
            $this->getConfig(),
            $this->getProcessorMessagePlugins()
        );
    }

    /**
     * @return QueueClientInterface
     */
    public function getQueueClient()
    {
        return $this->getProvidedDependency(QueueDependencyProvider::CLIENT_QUEUE);
    }

    /**
     * @return QueueMessageProcessorInterface[]
     */
    public function getProcessorMessagePlugins()
    {
        return $this->getProvidedDependency(QueueDependencyProvider::CLIENT_MESSAGE_PROCESSOR_PLUGIN);
    }
}