<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2\Connector;

use Daikon\Dbal\Connector\ConnectorInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

final class CouchDb2Connector implements ConnectorInterface
{
    private $settings;

    private $connection;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
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

    public function getSettings(): array
    {
        return $this->settings;
    }

    private function connect()
    {
        $baseUri = sprintf(
            '%s://%s:%s',
            $this->settings['transport'],
            $this->settings['host'],
            $this->settings['port']
        );

        $clientOptions = ['base_uri' => $baseUri];

        if (isset($this->settings['debug'])) {
            $clientOptions['debug'] = $this->settings['debug'] === true;
        }

        if (isset($this->settings['auth'])
            && !empty($this->settings['auth']['username'])
            && !empty($this->settings['auth']['password'])
        ) {
            $clientOptions['auth'] = [
                $this->settings['auth']['username'],
                $this->settings['auth']['password'],
                $this->settings['auth']['type'] ?? 'basic'
            ];
        }

        if (isset($this->settings['default_headers'])) {
            $clientOptions['headers'] = $this->settings['default_headers'];
        }

        if (isset($this->settings['default_options'])) {
            $clientOptions = array_merge($clientOptions, $this->settings['default_options']);
        }

        if (isset($this->settings['default_query'])) {
            $handler = HandlerStack::create();
            $handler->push(Middleware::mapRequest(
                function (RequestInterface $request) {
                    $uri = $request->getUri();
                    foreach ($this->settings['default_query'] as $param => $value) {
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
