<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Queue\Dependency\Plugin;

use Generated\Shared\Transfer\QueueMessageTransfer;

interface QueueMessageProcessorInterface
{

    /**
     * @param QueueMessageTransfer[] $queueMessageTransfers
     *
     * @return QueueMessageTransfer[]
     */
    public function processMessages(array $queueMessageTransfers);

    /**
     * @return int
     */
    public function getChunkSize();
}