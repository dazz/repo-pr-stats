<?php
/*
 * This file is part of the RepoPrStats application.
 *
 * (c) Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service\Storage\Adapter;

use Aws\S3\S3Client;

/**
 * Class S3System
 * @package Dazz\PrStats\Service\Storage
 */
class S3 implements StorageAdapterInterface
{
    /**
     * @type string
     */
    private $bucket;

    /**
     * @param string $bucket
     */
    public function __construct($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * @param $repositorySlug
     * @return string
     */
    public function getRepositoryDirectory($repositorySlug)
    {
        return sprintf('s3://%s/%s', $this->bucket, $repositorySlug);
    }
}
