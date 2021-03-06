<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Queue\Business\Worker;

use Spryker\Shared\Queue\QueueConfig as SharedConfig;
use Spryker\Zed\Queue\Business\Process\ProcessManagerInterface;
use Spryker\Zed\Queue\QueueConfig;

/**
 * @method \Spryker\Zed\Queue\Business\QueueBusinessFactory getFactory()
 */
class Worker implements WorkerInterface
{

    const DEFAULT_MAX_QUEUE_WORKER = 1;
    const SECOND_TO_MILLISECONDS = 1000;
    const PROCESS_BUSY = 'busy';
    const PROCESS_NEW = 'new';
    const PROCESSES_INTSTANCES = 'processes';

    /**
     * @var \Spryker\Zed\Queue\Business\Process\ProcessManagerInterface
     */
    protected $processManager;

    /**
     * @var \Spryker\Zed\Queue\QueueConfig
     */
    protected $queueConfig;

    /**
     * @var \Spryker\Zed\Queue\Business\Worker\WorkerProgressBarInterface
     */
    protected $workerProgressBar;

    /**
     * @var array
     */
    protected $queueNames;

    /**
     * @param \Spryker\Zed\Queue\Business\Process\ProcessManagerInterface $processManager
     * @param \Spryker\Zed\Queue\QueueConfig $queueConfig
     * @param \Spryker\Zed\Queue\Business\Worker\WorkerProgressBarInterface $workerProgressBar
     * @param array $queueNames
     */
    public function __construct(
        ProcessManagerInterface $processManager,
        QueueConfig $queueConfig,
        WorkerProgressBarInterface $workerProgressBar,
        array $queueNames
    ) {
        $this->processManager = $processManager;
        $this->workerProgressBar = $workerProgressBar;
        $this->queueConfig = $queueConfig;
        $this->queueNames = $queueNames;
    }

    /**
     * @param string $command
     * @param int $round
     * @param array $processes
     *
     * @return void
     */
    public function start($command, $round = 1, $processes = [])
    {
        $startTime = time();
        $passedSeconds = 0;
        $maxThreshold = (int)$this->queueConfig->getQueueWorkerMaxThreshold();
        $delayIntervalSeconds = (int)$this->queueConfig->getQueueWorkerInterval();
        $this->workerProgressBar->start($maxThreshold, $round);

        $pendingProcesses = [];
        while ($passedSeconds < $maxThreshold) {
            $processes = array_merge($this->executeOperation($command), $processes);
            $pendingProcesses = $this->getPendingProcesses($processes);
            $this->workerProgressBar->advance();

            usleep($delayIntervalSeconds * static::SECOND_TO_MILLISECONDS);
            $passedSeconds = time() - $startTime;
        }

        $this->workerProgressBar->finish();
        $this->waitForPendingProcesses($pendingProcesses, $command, $round, $delayIntervalSeconds);

        $this->processManager->flushIdleProcesses();
    }

    /**
     * @param \Symfony\Component\Process\Process[] $processes
     * @param string $command
     * @param int $round
     * @param int $delayIntervalSeconds
     *
     * @return void
     */
    protected function waitForPendingProcesses(array $processes, $command, $round, $delayIntervalSeconds)
    {
        usleep($delayIntervalSeconds * static::SECOND_TO_MILLISECONDS);
        $pendingProcesses = $this->getPendingProcesses($processes);

        if (count($pendingProcesses) > 0) {
            $this->workerProgressBar->reset();
            $this->start($command, ++$round, $pendingProcesses);
        }
    }

    /**
     * @param \Symfony\Component\Process\Process[] $processes
     *
     * @return \Symfony\Component\Process\Process[]
     */
    protected function getPendingProcesses($processes)
    {
        $pendingProcesses = [];
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $pendingProcesses[] = $process;
            }
        }

        return $pendingProcesses;
    }

    /**
     * @param string $command
     *
     * @return \Symfony\Component\Process\Process[]
     */
    protected function executeOperation($command)
    {
        $this->workerProgressBar->refreshOutput(count($this->queueNames));

        $index = 0;
        $processes = [];
        foreach ($this->queueNames as $queue) {
            $processCommand = sprintf('%s %s >> %s', $command, $queue, $this->queueConfig->getQueueWorkerOutputFileName());
            $queueProcesses = $this->startProcesses($processCommand, $queue);
            $processes = array_merge($processes,  $queueProcesses[self::PROCESSES_INTSTANCES]);

            $this
                ->workerProgressBar
                ->writeConsoleMessage(
                    ++$index,
                    $queue,
                    $queueProcesses[self::PROCESS_BUSY],
                    $queueProcesses[self::PROCESS_NEW]
                );
        }

        return $processes;
    }

    /**
     * @param string $command
     * @param string $queue
     *
     * @return array
     */
    protected function startProcesses($command, $queue)
    {
        $busyProcessNumber = $this->processManager->getBusyProcessNumber($queue);
        $numberOfWorkers = $this->getMaxQueueWorker($queue) - $busyProcessNumber;

        $processes = [];
        for ($i = 0; $i < $numberOfWorkers; $i++) {
            $processes[] = $this->processManager->triggerQueueProcess($command, $queue);
        }

        return [
            self::PROCESS_BUSY => $busyProcessNumber,
            self::PROCESS_NEW => $numberOfWorkers,
            self::PROCESSES_INTSTANCES => $processes,
        ];
    }

    /**
     * @param string $queueName
     *
     * @return int
     */
    protected function getMaxQueueWorker($queueName)
    {
        $adapterConfiguration = $this->queueConfig->getQueueAdapterConfiguration();

        if (!array_key_exists($queueName, $adapterConfiguration)) {
            return static::DEFAULT_MAX_QUEUE_WORKER;
        }
        $queueAdapterConfiguration = $adapterConfiguration[$queueName];

        if (!array_key_exists(SharedConfig::CONFIG_MAX_WORKER_NUMBER, $queueAdapterConfiguration)) {
            return static::DEFAULT_MAX_QUEUE_WORKER;
        }

        return $queueAdapterConfiguration[SharedConfig::CONFIG_MAX_WORKER_NUMBER];
    }

}
