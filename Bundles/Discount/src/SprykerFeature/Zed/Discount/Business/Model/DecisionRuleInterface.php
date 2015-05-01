<?php

namespace SprykerFeature\Zed\Discount\Business\Model;

use SprykerFeature\Zed\Discount\Dependency\Plugin\DiscountDecisionRulePluginInterface;
use SprykerFeature\Shared\Discount\Dependency\Transfer\DiscountableContainerInterface;
use SprykerFeature\Zed\Discount\Persistence\Propel\SpyDiscount;

interface DecisionRuleInterface
{
    /**
     * @param SpyDiscount $discountEntity
     * @param DiscountableContainerInterface $discountableContainer
     * @param DiscountDecisionRulePluginInterface[] $decisionRulePlugins
     * @return bool
     */
    public function evaluate(
        SpyDiscount $discountEntity,
        DiscountableContainerInterface $discountableContainer,
        array $decisionRulePlugins
    );
}