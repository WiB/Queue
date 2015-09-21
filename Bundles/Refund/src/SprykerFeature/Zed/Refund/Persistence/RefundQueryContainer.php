<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Refund\Persistence;

use Generated\Zed\Ide\FactoryAutoCompletion\RefundPersistence;
use SprykerEngine\Zed\Kernel\Persistence\AbstractQueryContainer;
use SprykerFeature\Zed\Refund\Persistence\Propel\SpyRefundQuery;

/**
 * @method RefundPersistence getFactory()
 */
class RefundQueryContainer extends AbstractQueryContainer implements RefundQueryContainerInterface
{

    public function queryRefundsByIdSalesOrder($idSalesOrder)
    {
        $query = SpyRefundQuery::create();
        $query->filterByFkSalesOrder($idSalesOrder);

        return $query;
    }

    /**
     * @param int $idMethod
     *
     * @return SpyRefundQuery
     */
    public function queryRefundByIdRefund($idMethod)
    {
        $query = $this->queryMethods();
        $query->filterByIdRefund($idMethod);

        return $query;
    }

}