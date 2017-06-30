<?php

namespace Dailex\Service;

use Dailex\Service\Provisioner\DefaultProvisioner;

final class ServiceDefinition implements ServiceDefinitionInterface
{
    private $serviceClass;

    private $provisionerClass;

    private $provisionerSettings;

    private $subscriptions;

    public function __construct(
        string $serviceClass,
        string $provisionerClass = null,
        array $provisionerSettings = [],
        array $subscriptions = []
    ) {
        $this->serviceClass = $serviceClass;
        $this->provisionerClass = $provisionerClass ?? DefaultProvisioner::class;
        $this->provisionerSettings = $provisionerSettings;
        $this->subscriptions = $subscriptions;
    }

    public function getServiceClass(): string
    {
        return $this->serviceClass;
    }

    public function getProvisionerClass(): string
    {
        return $this->provisionerClass;
    }

    public function getProvisionerSettings(): array
    {
        return $this->provisionerSettings;
    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
