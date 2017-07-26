<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Mailer\MailerServiceInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Silex\Provider\SwiftmailerServiceProvider;

final class SwiftmailerProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $mailerConfig = $configProvider->get('mailers');

        $app->register(new SwiftmailerServiceProvider);

        //@todo delegate all bootstrap
        if (isset($mailerConfig['swiftmailer']['options'])) {
            $app['swiftmailer.options'] = $mailerConfig['swiftmailer']['options'];
        }

        if (isset($mailerConfig['swiftmailer']['use_spool'])) {
            $app['swiftmailer.use_spool'] = $mailerConfig['swiftmailer']['use_spool'];
        }

        if (isset($mailerConfig['swiftmailer']['delivery_addresses'])) {
            $app['swiftmailer.delivery_addresses'] = $mailerConfig['swiftmailer']['delivery_addresses'];
        }

        if (isset($mailerConfig['swiftmailer']['transport'])
            && class_exists($mailerConfig['swiftmailer']['transport'])
        ) {
            $app['swiftmailer.transport'] = new $mailerConfig['swiftmailer']['transport'];
        }

        $injector
            ->share($serviceClass)
            ->alias(MailerServiceInterface::class, $serviceClass)
            ->delegate(
                $serviceClass,
                function (LoggerInterface $logger) use ($serviceClass, $app, $mailerConfig) {
                    return new $serviceClass($app['mailer'], $mailerConfig, $logger);
                }
            );
    }
}
