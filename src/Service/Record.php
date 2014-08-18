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

use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Record
 * @package Dazz\PrStats\Service
 */
class Record
{
    /** @var array */
    private $pullRequests = [];

    /** @var string */
    private $name;

    public function __construct(array $pullRequests)
    {
        $this->pullRequests = $pullRequests;
    }

    /**
     * @param SplFileInfo $file
     * @return Record
     */
    public static function createFromFile(SplFileInfo $file)
    {
        $record = new self(json_decode($file->getContents()));
        $record->setName($file->getRelativePathname());
        return $record;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getPullRequests()
    {
        return $this->pullRequests;
    }

    /**
     *
     * @param int $index
     * @param array $stats
     */
    public function addPullRequestStats($index, $stats)
    {
        $this->pullRequests[$index]->data_stats = $stats;
    }

    /**
     * @param $index
     * @return mixed
     */
    public function getPullRequestStats($index)
    {
        return $this->pullRequests[$index]->data_stats;
    }

    /**
     * @param int $index
     * @param array $pullRequestDetail
     */
    public function addPullRequestDetail($index, $pullRequestDetail)
    {
        $this->pullRequests[$index]->data = $pullRequestDetail;
    }

    /**
     * @param int $index
     * @return \StdClass
     */
    public function getPullRequestDetail($index)
    {
        return $this->pullRequests[$index]->data;
    }

    /**
     * @param int $index
     * @param array $pullRequestStatus
     */
    public function addPullRequestStatus($index, $pullRequestStatus)
    {
        $this->pullRequests[$index]->data_statuses = $pullRequestStatus;
    }

    /**
     * @return string
     */
    public function dumpJson()
    {
        return json_encode($this->getPullRequests(), JSON_PRETTY_PRINT);
    }
} 