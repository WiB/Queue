<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Maintenance\Business\DependencyTree\DependencyFinder;

use Spryker\Zed\Maintenance\Business\DependencyTree\DependencyTree;
use Symfony\Component\Finder\SplFileInfo;

class LocatorClient extends AbstractDependencyFinder
{

    const NO_LAYER = 'Default';
    const BUNDLE = 'bundle';

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $fileInfo
     *
     * @throws \Exception
     *
     * @return void
     */
    public function addDependencies(SplFileInfo $fileInfo)
    {
        $content = $fileInfo->getContents();

        if (!preg_match_all('/->(?<bundle>.*?)\(\)->client\(\)/', $content, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $toBundle = $match[self::BUNDLE];

            if (preg_match('/->/', $toBundle)) {
                $foundParts = explode('->', $toBundle);
                $toBundle = array_pop($foundParts);
            }

            $toBundle = ucfirst($toBundle);
            $foreignClassName = $this->getClassName($toBundle);
            $dependencyInformation = [
                DependencyTree::META_FOREIGN_LAYER => self::NO_LAYER,
                DependencyTree::META_FOREIGN_CLASS_NAME => $foreignClassName,
            ];
            $this->addDependency($fileInfo, $toBundle, $dependencyInformation);
        }
    }

    /**
     * @param string $bundle
     *
     * @return string
     */
    private function getClassName($bundle)
    {
        return sprintf('Spryker\\Client\\%1$s\\\\%1$sClient', $bundle);
    }

}