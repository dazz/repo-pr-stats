<?php
/**
 * This file is part of the RepoPrStats application.
 *
 * (c) {YEAR} Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service\Storage\Adapter;

/**
 * Interface StorageAdapterInterface
 * @package Dazz\PrStats\Service\Storage\Adapter
 */
interface StorageAdapterInterface
{
    /**
     * @param string $repositorySlug
     * @return string
     */
    public function getRepositoryDirectory($repositorySlug);
} 