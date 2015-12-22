<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Glossary;

use Spryker\Zed\Application\Communication\Plugin\Pimple;
use Spryker\Zed\Glossary\Dependency\Facade\GlossaryToMessengerBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Glossary\Dependency\Facade\GlossaryToLocaleBridge;
use Spryker\Zed\Glossary\Dependency\Facade\GlossaryToTouchBridge;

class GlossaryDependencyProvider extends AbstractBundleDependencyProvider
{

    const FACADE_TOUCH = 'touch facade';

    const FACADE_LOCALE = 'locale facade';

    const PLUGIN_VALIDATOR = 'validator plugin';

    const FACADE_MESSENGER = 'messages';

    /**
     * @param Container $container
     *
     * @return Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container[self::FACADE_LOCALE] = function (Container $container) {
            return new GlossaryToLocaleBridge($container->getLocator()->locale()->facade());
        };

        $container[self::PLUGIN_VALIDATOR] = function () {
            return (new Pimple())->getApplication()['validator'];
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container[self::FACADE_TOUCH] = function (Container $container) {
            return new GlossaryToTouchBridge($container->getLocator()->touch()->facade());
        };

        $container[self::FACADE_LOCALE] = function (Container $container) {
            return new GlossaryToLocaleBridge($container->getLocator()->locale()->facade());
        };

        $container[self::FACADE_MESSENGER] = function (Container $container) {
            return new GlossaryToMessengerBridge($container->getLocator()->messenger()->facade());
        };

        return $container;
    }

}