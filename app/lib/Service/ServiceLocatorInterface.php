<?php

namespace Dailex\Service;

use Psr\Container\ContainerInterface;

interface ServiceLocatorInterface extends ContainerInterface
{
    public function make($implementor, array $state = []);

    public function getServiceDefinitionMap(): ServiceDefinitionMap;
}
