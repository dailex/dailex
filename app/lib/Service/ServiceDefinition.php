<?php

namespace Dailex\Service;

use Dailex\Service\Provisioner\DefaultProvisioner;

final class ServiceDefinition implements ServiceDefinitionInterface
{
    private $serviceClass;

    private $provisionerClass;

    private $provisionerSettings;

    public function __construct(
        string $serviceClass,
        string $provisionerClass = DefaultProvisioner::class,
        array $provisionerSettings = []
    ) {
        $this->serviceClass = $serviceClass;
        $this->provisionerClass = $provisionerClass;
        $this->provisionerSettings = $provisionerSettings;
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
}
