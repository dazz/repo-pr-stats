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
 * Class PullRequestServiceProvider
 * @package Dazz\PrStats\Service
 */
class RepositoryServiceProvider implements ServiceProviderInterface
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
        $app['repository.recorder'] = $app->factory(
            function () use ($app) {
                return new RepositoryRecorder($app['repository.host'], $app['storage'], $app['github.repositories']);
            }
        );

        $app['repository.host'] = $app->factory(
            function () use ($app) {
                return new RepositoryHost($app['github.token']);
            }
        );

        $app['github.token'] = $app->factory(
            function () use ($app) {
                if ($app->offsetExists('config.github.token') && !empty($app['config.github.token'])) {
                    return $app['config.github.token'];
                }
                throw new \UnexpectedValueException('config.github.token is missing');
            }
        );

        $app['github.repositories'] = $app->factory(
            function () use ($app) {
                if ($app->offsetExists('config.github.repositories') && !empty($app['config.github.repositories'])) {
                    return array_combine(
                        array_map(
                            function ($repository) {
                                return str_replace('/', '-', $repository);
                            },
                            $app['config.github.repositories']
                        ),
                        $app['config.github.repositories']
                    );
                }
                throw new \UnexpectedValueException('config.github.repositories are missing');
            }
        );

        $app['github.repository'] = $app->protect(
            function ($repository) use ($app) {
                if (isset($app['github.repositories'][$repository])) {
                    return $app['github.repositories'][$repository];
                }

                throw new \UnexpectedValueException('repository is not defined in config.github.repositories');
            }
        );
    }
}