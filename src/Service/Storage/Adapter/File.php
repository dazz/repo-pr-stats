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

/**
 * Class File
 * @package Dazz\PrStats\Service\Storage\Adapter
 */
class File implements StorageAdapterInterface
{
    /** @var  string */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param $repositorySlug
     * @return string
     */
    public function getRepositoryDirectory($repositorySlug)
    {
        return sprintf('%s/%s', $this->path, $repositorySlug);
    }
} 