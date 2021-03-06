<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Queue\Business\Task;

use Spryker\Client\Queue\QueueClientInterface;
use Spryker\Zed\Queue\Business\Exception\MissingQueuePluginException;
use Spryker\Zed\Queue\QueueConfig;

class TaskManager implements TaskManagerInterface
{

    /**
     * @var \Spryker\Client\Queue\QueueClientInterface
     */
    protected $client;

    /**
     * @var \Spryker\Zed\Queue\QueueConfig
     */
    protected $queueConfig;

    /**
     * @var \Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface[]
     */
    protected $messageProcessorPlugins;

    /**
     * @param \Spryker\Client\Queue\QueueClientInterface $client
     * @param \Spryker\Zed\Queue\QueueConfig $queueConfig
     * @param \Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface[] $messageProcessorPlugins
     */
    public function __construct(QueueClientInterface $client, QueueConfig $queueConfig, array $messageProcessorPlugins)
    {
        $this->client = $client;
        $this->queueConfig = $queueConfig;
        $this->messageProcessorPlugins = $messageProcessorPlugins;
    }

    /**
     * @param string $queueName
     *
     * @return void
     */
    public function run($queueName)
    {
        $processorPlugin = $this->getQueueProcessorPlugin($queueName);
        $queueOptions = $this->getQueueReceiverOptions($queueName);
        $messages = $this->receiveMessages($queueName, $processorPlugin->getChunkSize(), $queueOptions);
        if ($messages === null) {
            return;
        }

        $processedMessages = $processorPlugin->processMessages($messages);
        if ($processedMessages === null) {
            return;
        }

        $this->postProcessMessages($processedMessages);
    }

    /**
     * @param string $queueName
     *
     * @throws \Spryker\Zed\Queue\Business\Exception\MissingQueuePluginException
     *
     * @return \Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface
     */
    protected function getQueueProcessorPlugin($queueName)
    {
        if (!array_key_exists($queueName, $this->messageProcessorPlugins)) {
            throw new MissingQueuePluginException(
                sprintf(
                    'There is no message processor plugin registered for this queue: %s, ' .
                    'you can fix this error by adding it in QueueDependencyProvider',
                    $queueName
                )
            );
        }

        return $this->messageProcessorPlugins[$queueName];
    }

    /**
     * @param string $queueName
     *
     * @return array
     */
    protected function getQueueReceiverOptions($queueName)
    {
        return $this->queueConfig->getQueueReceiverOption($queueName);
    }

    /**
     * @param string $queueName
     * @param int $chunkSize
     * @param array|null $options
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer[]
     */
    public function receiveMessages($queueName, $chunkSize, array $options = null)
    {
        return $this->client->receiveMessages($queueName, $chunkSize, $options);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer[] $queueReceiveMessageTransfers
     *
     * @return void
     */
    protected function postProcessMessages(array $queueReceiveMessageTransfers)
    {
        foreach ($queueReceiveMessageTransfers as $queueReceiveMessageTransfer) {
            if ($queueReceiveMessageTransfer->getAcknowledge()) {
                $this->client->acknowledge($queueReceiveMessageTransfer);
            }

            if ($queueReceiveMessageTransfer->getReject()) {
                $this->client->reject($queueReceiveMessageTransfer);
            }

            if ($queueReceiveMessageTransfer->getHasError()) {
                $this->client->handleError($queueReceiveMessageTransfer);
            }
        }
    }

}
