<?php
namespace Dazz\PrStats\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class StorageServiceProvider
 * @package Dazz\PrStats\Service
 */
class StorageServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple An Container instance
     */
    public function register(Container $pimple)
    {
        $pimple['cacheDir'] = sprintf('%s/prLog', $pimple['rootDir']);

        $pimple['storage'] = $pimple->factory(
            function () use ($pimple) {
                return new Storage($pimple['cacheDir']);
            }
        );
    }
}