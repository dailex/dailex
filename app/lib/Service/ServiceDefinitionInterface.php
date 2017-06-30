<?php

namespace Dailex\Service;

interface ServiceDefinitionInterface
{
    public function getServiceClass(): string;

    public function getProvisionerClass(): string;

    public function getProvisionerSettings(): array;

    public function getSubscriptions(): array;
}
