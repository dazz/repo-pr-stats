<?php
/*
 * This file is part of the RepoPrStats application.
 *
 * (c) Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service\Storage;

use Dazz\PrStats\Service\Record;
use Symfony\Component\Finder\Finder;

/**
 * Interface StorageInterface
 * @package Dazz\PrStats\Service\Storage
 */
interface StorageInterface
{
    /**
     * Get Record by name
     *
     * @param $repositorySlug
     * @param $filename
     * @return Record
     * @throws \Exception
     */
    public function getRecord($repositorySlug, $filename);

    /**
     * @param string $repositorySlug
     * @return Finder
     */
    public function getAllRecords($repositorySlug);

    /**
     * @param $repositorySlug
     * @return Record
     */
    public function getLastRecord($repositorySlug);

    /**
     * Persisting a Record to the storage
     *
     * @param string $repositorySlug
     * @param Record $record
     */
    public function storeRecord($repositorySlug, Record $record);
}
