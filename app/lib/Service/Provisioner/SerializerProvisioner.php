<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Dailex\Service\Provisioner\ProvisionerInterface;
use Pimple\Container;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializerProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        $app->register(new SerializerServiceProvider);

        $app->extend(
            'serializer.encoders',
            function ($encoders, $app) use ($injector, $provisionerSettings) {
                foreach (array_reverse($provisionerSettings['encoders'] ?? []) as $encoder) {
                    array_unshift($encoders, $injector->make($encoder));
                }
                return $encoders;
            }
        );

        $app->extend(
            'serializer.normalizers',
            function ($normalizers, $app) use ($injector, $provisionerSettings) {
                foreach (array_reverse($provisionerSettings['normalizers'] ?? []) as $normalizer) {
                    array_unshift($normalizers, $injector->make($normalizer));
                }
                return $normalizers;
            }
        );

        $injector->delegate(
            $serviceClass,
            function () use ($app) {
                return $app['serializer'];
            }
        )
        ->share($serviceClass)
        ->alias(SerializerInterface::class, $serviceClass);
    }
}
