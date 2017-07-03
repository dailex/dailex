<?php

namespace Dailex\Infrastructure\DataAccess\Storage;

interface StorageAdapterInterface
{
    public function read();

    public function write();

    public function delete();
}
