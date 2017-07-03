<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2\Connector;

use Dailex\Infrastructure\DataAccess\Connector\ConnectorInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

class CouchDb2Connector implements ConnectorInterface
{
    private $config;

    private $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connect();
        }

        return $this->connection;
    }

    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->connection = null;
        }
    }

    private function connect()
    {
        $baseUri = sprintf(
            '%s://%s:%s',
            $this->config['transport'],
            $this->config['host'],
            $this->config['port']
        );

        $clientOptions = ['base_uri' => $baseUri];

        if (isset($this->config['debug'])) {
            $clientOptions['debug'] = $this->config['debug'] === true;
        }

        if (isset($this->config['auth'])) {
            if (!empty($this->config['auth']['username'])) {
                $clientOptions['auth'] = [
                    $this->config['auth']['username'],
                    $this->config['auth']['password'],
                    $this->config['auth']['type'] ?? 'basic'
                ];
            }
        }

        if (isset($this->config['default_headers'])) {
            $clientOptions['headers'] = $this->config['default_headers'];
        }

        if (isset($this->config['default_options'])) {
            $clientOptions = array_merge($clientOptions, $this->config['default_options']);
        }

        if (isset($this->config['default_query'])) {
            $handler = HandlerStack::create();
            $handler->push(Middleware::mapRequest(
                function (RequestInterface $request) {
                    $uri = $request->getUri();
                    foreach ($this->config['default_query'] as $param => $value) {
                        $uri = Uri::withQueryValue($uri, $param, $value);
                    }
                    return $request->withUri($uri);
                }
            ));
            $clientOptions['handler'] = $handler;
        }

        return new Client($clientOptions);
    }
}
