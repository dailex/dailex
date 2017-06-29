<?php

namespace Dailex\Service;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface ServiceProvisionerInterface
{
    public function provision(): ServiceLocatorInterface;

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher): void;
}
