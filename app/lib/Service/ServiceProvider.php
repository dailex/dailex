<?php

namespace Dailex\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    private $serviceProvisioner;

    public function __construct(ServiceProvisionerInterface $serviceProvisioner)
    {
        $this->serviceProvisioner = $serviceProvisioner;
    }

    public function register(Container $app)
    {
        $app['dailex.service_locator'] = $this->serviceProvisioner->provision();
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $this->serviceProvisioner->subscribe($app, $dispatcher);
    }
}
