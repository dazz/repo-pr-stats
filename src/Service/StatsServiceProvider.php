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
                return new Stats($app['storage'], $app['stats.measureWeight']);
            }
        );
        $app['stats.measureWeight'] = $app->factory(
            function () use ($app) {
                return new StatsWeight($app['stats.measureWeight.config']);
            }
        );

        $app['stats.measureWeight.config'] = $app->factory(
            function () {
                return [
                    'age_3' => 10, // @TODO: make it exponential!
                    'age_10' => 20,
                    'age_unlimited' => 100,
                    'not_mergeable' => 10,
                    'mergeable_state_not_clean' => 10,
                    'no_assignee' => 10,
                    'empty_body' => 10,
                ];
            }
        );
    }
}
