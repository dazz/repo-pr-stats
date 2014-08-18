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
 * Class RepositoryHost
 * @package Dazz\PrStats\Service
 */
class RepositoryHost
{
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        $response = self::request($url, $this->token);
        return json_decode($response->getBody());
    }

    /**
     * @param string $url
     * @param string $token
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    private static function request($url, $token)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->createRequest('GET', $url);
        $request->addHeader('Authorization', sprintf('token %s', $token));
        return $client->send($request);
    }
}
