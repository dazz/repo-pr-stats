<?php
/**
 * This file is part of the RepoPrStats application.
 *
 * (c) {YEAR} Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service\Storage;

use Dazz\PrStats\Service\Exception\NoRecordCreatedException;
use Dazz\PrStats\Service\Record;
use Dazz\PrStats\Service\Storage\Adapter\StorageAdapterInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class Storage
 * @package Dazz\PrStats\Service\Storage
 */
class Storage implements StorageInterface
{
    /**
     * @type Adapter\StorageAdapterInterface
     */
    private $adapter;

    public function __construct(StorageAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param $repositorySlug
     * @param $filename
     * @return Record
     * @throws \Exception
     */
    public function getRecord($repositorySlug, $filename)
    {
        $files = Finder::create()
            ->files()
            ->in($this->adapter->getRepositoryDirectory($repositorySlug))
            ->name(sprintf('*%s*.json', $filename))
        ;

        foreach ($files as $file) {
            return Record::createFromFile($file);
        }
    }

    /**
     * @param string $repositorySlug
     * @return Finder
     *
     * @throws NoRecordCreatedException
     */
    public function getAllRecords($repositorySlug)
    {
        $directory = $this->adapter->getRepositoryDirectory($repositorySlug);
        if (!is_dir($directory)) {
            throw new NoRecordCreatedException($repositorySlug);
        }

        return Finder::create()
            ->files()
            ->in($directory)
            ->name('*.json')
            ->sortByName()
            ;
    }

    /**
     * @param $repositorySlug
     * @return Record
     */
    public function getLastRecord($repositorySlug)
    {
        $finder = $this->getAllRecords($repositorySlug);

        foreach ($finder as $file) {
            //@TODO: use google or try yourself!
        }
        return Record::createFromFile($file);
    }

    /**
     * @param string $repositorySlug
     * @param Record $record
     */
    public function storeRecord($repositorySlug, Record $record)
    {
        $filename = $this->createFilename($repositorySlug);
        if (file_exists($filename) == false) {
            file_put_contents($filename, $record->dumpJson());
        }
    }

    /**
     * @param string $repositorySlug
     * @param string $time
     * @param string $timeFormat
     *
     * @return string
     */
    private function createFileName($repositorySlug, $time = 'now', $timeFormat = 'Y-m-d_H')
    {
        $format = (new \DateTime($time))->format($timeFormat);
        $folderName = $this->adapter->getRepositoryDirectory($repositorySlug);

        if (is_dir($folderName) == false) {
            mkdir($folderName, 0777, true);
        }

        return sprintf('%s/%s_%s.json', $folderName, $repositorySlug, $format);
    }
} 