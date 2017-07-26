<?php

namespace Dailex\Service;

interface ServiceDefinitionInterface
{
    public function getServiceClass(): string;

    public function getProvisionerClass(): string;

    public function getSettings(): array;

    public function getSubscriptions(): array;
}
