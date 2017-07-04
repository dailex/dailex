<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2\Storage;

use Daikon\Cqrs\Aggregate\DomainEventSequence;
use Daikon\Cqrs\EventStore\Commit;
use Daikon\Cqrs\EventStore\CommitSequence;
use Daikon\Cqrs\EventStore\CommitStreamId;
use Daikon\Cqrs\EventStore\CommitStreamRevision;
use Daikon\Dbal\Storage\StorageAdapterInterface;
use Daikon\MessageBus\Metadata\Metadata;
use Dailex\Exception\RuntimeException;
use Dailex\Infrastructure\DataAccess\CouchDb2\Connector\CouchDb2Connector;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

final class CouchDb2StorageAdapter implements StorageAdapterInterface
{
    private $connector;

    private $settings;

    public function __construct(CouchDb2Connector $connector, array $settings = [])
    {
        $this->connector = $connector;
        $this->settings = $settings;
    }

    public function read(string $identifier)
    {
        $viewPath = sprintf(
            '/_design/%s/_view/%s',
            $this->settings['design_doc'],
            $this->settings['view_name'] ?? 'commit_stream'
        );

        $viewParams = [
            'startkey' => sprintf('["%s", {}]', $identifier),
            'endkey' => sprintf('["%s", 1]', $identifier),
            'include_docs' => 'true',
            'reduce' => 'false',
            'descending' => 'true',
            'limit' => 1000 // @todo use snapshot size config setting as soon as available
        ];

        try {
            $response = $this->request($viewPath, 'GET', [], $viewParams);
            $rawResponse = json_decode($response->getBody(), true);
        } catch (RequestException $error) {
            if ($error->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                throw $error;
            }
        }

        if (!isset($rawResponse['total_rows'])) {
            throw new RuntimeException('Failed to read data for '.$identifier);
        }

        return CommitSequence::fromArray(array_map(function (array $commitData) {
            return $commitData['doc'];
        }, array_reverse($rawResponse['rows'])));
    }

    public function write(string $identifier, array $data)
    {
        $response = $this->request($identifier, 'PUT', $data);
        $rawResponse = json_decode($response->getBody(), true);

        if (!isset($rawResponse['ok']) || !isset($rawResponse['rev'])) {
            throw new RuntimeException('Failed to write data for '.$identifier);
        }
    }

    public function delete(string $identifier)
    {
    }

    private function request(string $identifier, string $method, array $body = [], array $params = [])
    {
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

        return $this->connector
            ->getConnection()
            ->send($request);
    }

    private function buildRequestUrl(string $identifier, array $params = [])
    {
        $settings = $this->connector->getSettings();
        $requestPath = sprintf('/%s/%s', $settings['database'], $identifier);
        if (!empty($params)) {
            $requestPath .= '?'.http_build_query($params);
        }
        return str_replace('//', '/', $requestPath);
    }
}
