<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Config\RoutingConfigLoader;
use Dailex\Controller\ControllerResolverServiceProvider;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class ControllerProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $appContext = $configProvider->get('app.context');
        $appEnv = $configProvider->get('app.env');
        $appConfigDir = $configProvider->get('app.config_dir');

        $app->register(new ControllerResolverServiceProvider);

        (new RoutingConfigLoader($app, $configProvider))->load(
            [
                $appConfigDir,
                // @todo get crate directories from config provider
                $configProvider->get('app.crates_dir').'/testing-blog/config'
            ],
            [
                'routing.php',
                "routing.$appContext.php",
                "routing.$appEnv.php",
                "routing.$appContext.$appEnv.php"
            ]
        );

        $injector->delegate(
            $serviceClass,
            function () use ($app) {
                return $app['controllers'];
            }
        )->share($serviceClass);
    }
}
