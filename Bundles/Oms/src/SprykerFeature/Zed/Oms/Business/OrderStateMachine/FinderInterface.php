<?php

namespace SprykerFeature\Zed\Oms\Business\OrderStateMachine;

use SprykerFeature\Zed\Sales\Persistence\Propel\SpySalesOrderItem;
use SprykerFeature\Zed\Sales\Persistence\Propel\SpySalesOrder;

interface FinderInterface
{
    /**
     * @param string $sku
     *
     * @return SpySalesOrderItem
     */
    public function getReservedOrderItemsForSku($sku);

    /**
     * @param $sku
     *
     * @return SpySalesOrderItem
     */
    public function countReservedOrderItemsForSku($sku);

    /**
     * @param SpySalesOrder $order
     *
     * @return array
     */
    public function getGroupedManuallyExecutableEvents(SpySalesOrder $order);

    /**
     * @param SpySalesOrder $order
     * @param string $flag
     *
     * @return SpySalesOrderItem[]
     */
    public function getItemsWithFlag(SpySalesOrder $order, $flag);

    /**
     * @param SpySalesOrder $order
     * @param string $flag
     *
     * @return SpySalesOrderItem[]
     */
    public function getItemsWithoutFlag(SpySalesOrder $order, $flag);

    /**
     * @return ProcessInterface[]
     */
    public function getProcesses();

    /**
     * @param SpySalesOrderItem $orderItem
     *
     * @return string
     */
    public function getStateDisplayName(SpySalesOrderItem $orderItem);
}