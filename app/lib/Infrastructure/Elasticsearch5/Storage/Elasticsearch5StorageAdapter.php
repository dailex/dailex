<?php

namespace Dailex\Infrastructure\Elasticsearch5\Storage;

use Daikon\Dbal\Storage\StorageAdapterInterface;
use Dailex\Infrastructure\Elasticsearch5\Connector\Elasticsearch5Connector;
use Elasticsearch\Common\Exceptions\Missing404Exception;

final class Elasticsearch5StorageAdapter implements StorageAdapterInterface
{
    private $connector;

    private $settings;

    public function __construct(Elasticsearch5Connector $connector, array $settings = [])
    {
        $this->connector = $connector;
        $this->settings = $settings;
    }

    public function read(string $identifier)
    {
        try {
            $document = $this->connector->getConnection()->get([
                'index' => $this->settings['index'],
                'type' => $this->settings['type'],
                'id' => $identifier
            ]);
        } catch (Missing404Exception $error) {
            return null;
        }

        $projectionClass = $document['_source']['@type'];
        return $projectionClass::fromArray($document['_source']);
    }

    public function write(string $identifier, array $data)
    {
        $document = [
            'index' => $this->settings['index'],
            'type' => $this->settings['type'],
            'id' => $identifier,
            'body' => $data
        ];

        $this->connector->getConnection()->index($document);

        return true;
    }

    public function delete(string $identifier)
    {
    }
}
