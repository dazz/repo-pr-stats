<?php
/*
 * This file is part of the RepoPrStats application.
 *
 * (c) Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class StatsServiceProvider
 * @package Dazz\PrStats\Service
 */
class StatsServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app An Container instance
     */
    public function register(Container $app)
    {
        $app['stats'] = $app->factory(
            function () use ($app) {
                return new Stats($app['storage'], $app['stats.measureWeights']);
            }
        );

        $app['stats.measureWeights'] = $app->factory(
            function () {
                return [
                    'age' => 10, // @TODO: make it exponential!
                    'mergeable' => [
                        'yes' => 0,
                        'no' => 10
                    ],
                    'mergeable_state' => [
                        'unknown' => 20,
                        'unstable' => 10, // mergeable, but  fails
                        'dirty' => 10,    // unmergeable
                        'clean' => 0,
                    ],
                    'assignee' => [
                        'yes' => 0,
                        'no' => 10
                    ],
                    'body' => [
                        'yes' => 0,
                        'no' => 10
                    ],
                ];
            }
        );
    }
}
