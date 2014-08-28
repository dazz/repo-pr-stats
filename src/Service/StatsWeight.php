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
 * Class StatsWeight
 * @package Dazz\PrStats\Service
 */
class StatsWeight
{
    /** @type array */
    private $config;

    public function __construct(array $weightConfig)
    {
        $this->config = $weightConfig;
    }

    /**
     * @param string $createdAt
     * @param string $dateString
     *
     * @return int
     */
    public function getAge($createdAt, $dateString)
    {
        list($date, $time) = explode('T', $createdAt);
        $days = (new \DateTime($date))->diff(new \DateTime($dateString))->format('%a');

        if ($days == 1) {
            return 0;
        }
        if ($days <= 3) {
            return $this->config['age_3'] * $days;
        }
        if ($days <= 10) {
            return $this->config['age_10'] * $days;
        }
        return $this->config['age_unlimited'] * $days * $days;
    }

    /**
     * @param bool $isMergeable
     * @return int
     */
    public function getNotMergeable($isMergeable)
    {
        if ($isMergeable) {
            return 0;
        }
        return $this->config['not_mergeable'];
    }

    /**
     * @param string $mergeState
     * @return int
     */
    public function getMergeStateNotClean($mergeState)
    {
        if ($mergeState == 'clean') {
            return 0;
        }
        return $this->config['mergeable_state_not_clean'];
    }

    /**
     * @param \stdClass $assignee
     * @return int
     */
    public function getNoAssignee($assignee)
    {
        if ($this->isAssigneeEmpty($assignee)) {
            return $this->config['no_assignee'];
        }
        return 0;
    }

    /**
     * @param string $body
     * @return int
     */
    public function getEmptyBody($body)
    {
        if (empty($body)) {
            return $this->config['empty_body'];
        }
        return 0;
    }

    /**
     * @param $assignee
     * @return bool
     */
    private function isAssigneeEmpty($assignee)
    {
        if (empty($assignee)) {
            return true;
        }
        if (property_exists($assignee, 'login') == false) {
            return true;
        }
        return  empty($assignee->login);
    }
} 