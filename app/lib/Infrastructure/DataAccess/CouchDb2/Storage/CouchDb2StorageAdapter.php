<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2\Storage;

use Dailex\Exception\RuntimeException;
use Dailex\Infrastructure\DataAccess\CouchDb2\Connector\CouchDb2Connector;
use Dailex\Infrastructure\DataAccess\Storage\StorageAdapterInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

final class CouchDb2StorageAdapter implements StorageAdapterInterface
{
    private $connector;

    public function __construct(CouchDb2Connector $connector)
    {
        $this->connector = $connector;
    }

    public function read()
    {
    }

    public function write(string $identifier, array $data)
    {
        $response = $this->request($identifier, 'PUT', $data);
        $rawResponse = json_decode($response->getBody(), true);

        if (!isset($rawResponse['ok']) || !isset($rawResponse['rev'])) {
            throw new RuntimeException('Failed to write data.');
        }
    }

    public function delete(string $identifier)
    {
    }

    private function request(string $identifier, string $method, array $body = [], array $params = [])
    {
        $client = $this->connector->getConnection();

        try {
            $requestPath = $this->buildRequestUrl($identifier, $params);
            if (empty($body)) {
                $request = new Request($method, $requestPath, ['Accept' => 'application/json']);
            } else {
                $request = new Request(
                    $method,
                    $requestPath,
                    ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
                    json_encode($body)
                );
            }
        } catch (GuzzleException $guzzleError) {
            throw new RuntimeException(
                sprintf('Failed to build %s request: %s', $method, $guzzleError),
                0,
                $guzzleError
            );
        }

        return $client->send($request);
    }

    private function buildRequestUrl(string $identifier, array $params = [])
    {
        $requestPath = sprintf('/%s/%s', $this->connector->getSettings()['database'], $identifier);
        if (!empty($params)) {
            $requestPath .= '?'.http_build_query($params);
        }
        return str_replace('//', '/', $requestPath);
    }
}
