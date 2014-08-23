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

/**
 * Class Stats
 * @package Dazz\PrStats\Service
 */
class Stats
{
    /** @type Storage */
    private $storage;
    /** @type StatsWeight */
    private $measureWeight;

    public function __construct(Storage $storage, StatsWeight $measureWeight)
    {
        $this->storage = $storage;
        $this->measureWeight = $measureWeight;
    }

    /**
     * @param $repositorySlug
     * @return array
     */
    public function getAllRepositoryStats($repositorySlug)
    {
        $stats = [];
        foreach ($this->storage->getAllRecords($repositorySlug) as $key => $file) {
            $record = Record::createFromFile($file);
            $stats[$key] = $this->getRecordStats($record);
        }
        return $stats;
    }

    /**
     * @param Record $record
     * @return array
     */
    public function getRecordStats(Record $record)
    {
        /** @var \SplFileInfo $file */
        $stat = $record->getMetaFromName();

        $stat['countPullRequests'] = $record->getNumberOfPullRequests();
        $stat['agePullRequests'] = 0;
        $stat['sum'] = 0;
        $stat['weights'] = [];

        foreach ($record->getPullRequests() as $index => $pullRequest) {

            $days = $record->getPullRequestAgeSince($index, $stat['date']);
            if ($days > $stat['agePullRequests']) {
                $stat['agePullRequests'] = $days;
            }

            $record->addPullRequestStats($index, $this->getPullRequestStats($pullRequest, $stat['date']));
            $stat['sum'] += $record->getPullRequestStats($index)['sum'];
        }
        return $stat;
    }

    /**
     * @param \stdClass $pullRequest
     * @param string    $dateString
     *
     * @return array
     */
    public function getPullRequestStats($pullRequest, $dateString = 'now')
    {
        $prDetail = $pullRequest->data;
        $score = [
            'age'             => $this->measureWeight->getAge($prDetail->created_at, $dateString),
            'mergeable'       => $this->measureWeight->getNotMergeable($prDetail->mergeable),
            'mergeable_state' => $this->measureWeight->getMergeStateNotClean($prDetail->mergeable_state),
            'assignee'        => $this->measureWeight->getNoAssignee($prDetail->assignee),
            'body'            => $this->measureWeight->getEmptyBody($prDetail->body),
        ];

        $score['sum'] = array_sum($score);

        return $score;
    }
} 