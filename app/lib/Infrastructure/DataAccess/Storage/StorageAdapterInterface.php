<?php

namespace Dailex\Infrastructure\DataAccess\Storage;

interface StorageAdapterInterface
{
    public function read();

    public function write(string $identifier, array $data);

    public function delete(string $identifier);
}
