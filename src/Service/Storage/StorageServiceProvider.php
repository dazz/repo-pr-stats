<?php
namespace Dazz\PrStats\Service\Storage;

use Dazz\PrStats\Service\Storage\Adapter\File;
use Dazz\PrStats\Service\Storage\Adapter\S3;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Aws\Common\Aws;

/**
 * Class StorageServiceProvider
 * @package Dazz\PrStats\Service\Storage
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
        $pimple['storage.config'] = function () use ($pimple) {
            return [
                'storage.system' => 's3', // file | s3
                'file.cacheDir' => sprintf('%s/prLog', $pimple['rootDir']),
                's3.client' => [
                    'key'    => 'your-aws-access-key-id',
                    'secret' => 'your-aws-secret-access-key',
                    'region' => 'us-east-1',
                ],
                's3.bucket' => '',
            ];
        };

        $pimple['storage'] = function () use ($pimple) {
            $storageSystem = sprintf('storage.%s', $pimple['storage.config']['storage.system']);
            if ($pimple->offsetExists($storageSystem)) {
                return new Storage($pimple[$storageSystem]);
            }
            throw new \Exception('Service '. $storageSystem. ' is not configured.');
        };

        $pimple['storage.file'] = function () use ($pimple) {
            return new File($pimple['storage.config']['file.cacheDir']);
        };

        $pimple['storage.s3'] = function () use ($pimple) {
            $pimple['storage.s3.client']->registerStreamWrapper();
            return new S3($pimple['storage.config']['s3.bucket']);
        };

        $pimple['storage.s3.client'] = function (Container $pimple) {
            $config = [];
            if (isset($pimple['storage.config']['s3.client'])) {
                $config = $pimple['storage.config']['s3.client'];
            }
            $aws = Aws::factory($config);
            return $aws->get('s3');
        };
    }
}