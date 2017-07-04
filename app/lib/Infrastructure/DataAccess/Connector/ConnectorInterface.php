<?php

namespace Dailex\Infrastructure\DataAccess\Connector;

interface ConnectorInterface
{
    public function getConnection();

    public function isConnected(): bool;

    public function disconnect(): void;

    public function getSettings(): array;
}
