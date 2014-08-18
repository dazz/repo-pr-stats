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
    /** @type array */
    private $measureWeights;

    public function __construct(Storage $storage, array $measureWeights)
    {
        $this->storage = $storage;
        $this->measureWeights = $measureWeights;
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
        $stat = [];

        $stat['countPullRequests'] = count($record->getPullRequests());

        list($filename, $extension) = explode('.', $record->getName());
        list($repo, $date, $hour) = explode('_', $filename);

        $stat['filename'] = $record->getName();
        $stat['date'] = $date;
        $stat['hour'] = $hour;

        $stat['agePullRequests'] = 0;
        $stat['sum'] = 0;
        $stat['weights'] = [];
        foreach ($record->getPullRequests() as $index => $pullRequest) {
            $data = $pullRequest->data;
            $days = (new \DateTime($data->created_at))->diff(new \DateTime($date))->format('%a');
            if ($days > $stat['agePullRequests']) {
                $stat['agePullRequests'] = $days;
            }
            $record->addPullRequestStats($index, $this->getPullRequestStats($pullRequest, $date));
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
        $weights = [];

        list($date, $time) = explode('T', $pullRequest->data->created_at);
        $days = (new \DateTime($date))->diff(new \DateTime($dateString))->format('%a');
        $weights['age'] = $this->measureWeights['age'] * $days;

        $mergeable = $pullRequest->data->mergeable ? 'yes' : 'no';
        $weights['mergeable'] = $this->measureWeights['mergeable'][$mergeable];

        $weights['mergeable_state'] = $this->measureWeights['mergeable_state'][$pullRequest->data->mergeable_state];

        $weights['assignee'] = empty($pullRequest->assignee->login) ? $this->measureWeights['assignee']['no'] : $this->measureWeights['assignee']['yes'];
        $weights['body'] = empty($pullRequest->body) ? $this->measureWeights['body']['no'] : $this->measureWeights['body']['yes'];

        $weights['sum'] = array_sum($weights);

        return $weights;
    }
} 