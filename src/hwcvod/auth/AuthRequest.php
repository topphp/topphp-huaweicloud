<?php

namespace hwcvod\auth;

use hwcvod\vod\client\VodClient;
use hwcvod\exception\VodException;
use hwcvod\vod\client\VodConfig;

class AuthRequest
{
    public $method  = '';
    public $scheme  = 'https';
    public $host    = '';
    public $uri     = '';
    public $query   = [];
    public $headers = [];
    public $body    = '';

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme(string $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        $this->host = VodConfig::$vodHost;
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     * @param $version
     * @param VodClient $vodClient
     * @throws VodException
     */
    public function setUri($uri, $version, VodClient $vodClient)
    {
        switch ($version) {
            case '1.0':
                $this->uri = '/v1.0/' . $vodClient->getVodConfig()->getProjectId() . $uri;
                break;
            case '1.1':
                $this->uri = '/v1.1/' . $vodClient->getVodConfig()->getProjectId() . $uri;
                break;
            default:
                throw new VodException("VOD.100011001", '不支持的接口版本！');
        }
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }
}
