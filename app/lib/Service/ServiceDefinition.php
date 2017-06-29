<?php

namespace Dailex\Service;

final class ServiceDefinition implements ServiceDefinitionInterface
{
    private $provisioner;

    private $class;

    public function __construct(array $config)
    {
        $this->provisioner = $config['provisioner'] ?? null;
        $this->class = $config['class']; //@todo required?
    }

    public function getProvisioner()
    {
        return $this->provisioner;
    }

    public function hasProvisioner()
    {
        return isset($this->provisioner);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function hasClass()
    {
        return isset($this->class);
    }
}
