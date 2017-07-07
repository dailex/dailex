<?php

namespace Dailex\Bootstrap;

use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Silex\Application;

final class ConsoleBootstrap extends Bootstrap
{
    public function __invoke(Application $app): void
    {
        $this->bootstrapConfig($app);
        $this->boostrapServices($app);

        // Update request context from environment for URL generation
        if ($hostName = getenv('HOST_NAME')) {
            $app['request_context']->setHost($hostName);
        }

        if ($hostScheme = getenv('HOST_SCHEME')) {
            $app['request_context']->setScheme($hostScheme);
        }

        if ($hostHttpPort = getenv('HOST_HTTP_PORT')) {
            $app['request_context']->setHttpPort($hostHttpPort);
        }

        if ($hostHttpsPort = getenv('HOST_HTTPS_PORT')) {
            $app['request_context']->setHttpsPort($hostHttpsPort);
        }
    }

    private function boostrapServices(Application $app): void
    {
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->configProvider);
        $app->register(new ServiceProvider($serviceProvisioner));
    }
}
