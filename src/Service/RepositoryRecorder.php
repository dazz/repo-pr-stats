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

use Dazz\PrStats\Service\Storage\StorageInterface;

/**
 * Class RepositoryRecorder
 * @package Dazz\PrStats\Service
 */
class RepositoryRecorder
{
    /** @type StorageInterface */
    private $storage;
    /** @type RepositoryHost */
    private $hoster;
    /** @type array */
    private $repositories;
    /** @type null|string */
    private $repositoryStartUrlPattern;

    /**
     * @param RepositoryHost   $hoster
     * @param StorageInterface $storage
     * @param array            $repositories
     * @param null             $repositoryStartUrlPattern
     */
    public function __construct(RepositoryHost $hoster, StorageInterface $storage, array $repositories, $repositoryStartUrlPattern = null)
    {
        $this->hoster = $hoster;
        $this->storage = $storage;

        if (!$repositoryStartUrlPattern) {
            $repositoryStartUrlPattern = 'https://api.github.com/repos/%s/pulls';
        }

        $this->repositoryStartUrlPattern = $repositoryStartUrlPattern;
        $this->repositories = $repositories;
    }

    /**
     * @param string $repositorySlug
     * @throws \Exception
     */
    public function createRecord($repositorySlug)
    {
        if (array_key_exists($repositorySlug, $this->repositories) == false) {
            throw new \Exception(sprintf('repository for %s is not configured in github.repositories', $repositorySlug));
        }

        $url = sprintf($this->repositoryStartUrlPattern, $this->repositories[$repositorySlug]);

        $record = new Record($this->hoster->get($url));

        foreach ($record->getPullRequests() as $index => $pr) {

            $record->addPullRequestDetail($index, $this->hoster->get($pr->url));
            $record->addPullRequestStatus($index, $this->hoster->get($record->getPullRequestDetail($index)->statuses_url));
        }
        $this->storage->storeRecord($repositorySlug, $record);
    }
}
