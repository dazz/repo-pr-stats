<?php
/*
 * This file is part of the RepoPrStats application.
 *
 * (c) Anne-Julia Scheuermann
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Dazz\PrStats\Service\Exception;

/**
 * Class NoRecordCreatedException
 * @package Dazz\PrStats\Service\Exception
 */
class NoRecordCreatedException extends \Exception
{
    /** @var string */
    private $repositorySlug;

    public function __construct($repositorySlug, $message = 'No records for this repository, yet!', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->repositorySlug = $repositorySlug;
    }

    /**
     * @return string
     */
    public function getRepositorySlug()
    {
        return $this->repositorySlug;
    }
}
