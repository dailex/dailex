<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Subscription;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportMap;
use Daikon\MessageBus\MessageBus;
use Daikon\MessageBus\MessageBusInterface;
use Dailex\Exception\ConfigException;
use Dailex\MessageBus\LazyHandler;
use Dailex\Service\ServiceDefinitionInterface;
use Dailex\Service\ServiceLocatorInterface;
use Pimple\Container;

final class MessageBusProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        if (!isset($provisionerSettings['transports'])) {
            throw new ConfigException('Message bus transports and channels configuration is required.');
        }

        $factory = function (ServiceLocatorInterface $serviceLocator) use ($provisionerSettings) {
            $transports = [];
            foreach ($provisionerSettings['transports'] as $transportName => $transportConfig) {
                $transports[$transportName] = new $transportConfig['class']($transportName);
            }
            $transports = new TransportMap($transports);

            $channelSubs = [
                'commands' => [],
                'commits' => [],
                'events' => []
            ];

            $serviceDefinitionMap = $serviceLocator->getServiceDefinitionMap();
            foreach ($serviceDefinitionMap->getIterator() as $serviceId => $serviceDefinition) {
                foreach ($serviceDefinition->getSubscriptions() as $subscriptionName => $subscriptionConfig) {
                    $channelName = $subscriptionConfig['channel'];
                    $transportName = $subscriptionConfig['transport'];
                    if (!$transports->has($transportName)) {
                        throw new ConfigException(
                            sprintf('Message bus transport "%s" has not been configured.', $transportName)
                        );
                    }

                    $lazyServiceHandler = new LazyHandler(function () use ($serviceLocator, $serviceId) {
                        return $serviceLocator->get($serviceId);
                    });
                    $channelSubs[$channelName][] = new Subscription(
                        $subscriptionName,
                        $transports->get($transportName),
                        new MessageHandlerList([$lazyServiceHandler])
                    );
                }
            }

            $channels = [];
            foreach ($channelSubs as $channelName => $subscriptions) {
                $channels[$channelName] = new Channel($channelName, new SubscriptionMap($subscriptions));
            }
            $channelMap = new ChannelMap($channels);

            return new MessageBus($channelMap);
        };

        $injector
            ->delegate($serviceClass, $factory)
            ->share($serviceClass)
            ->alias(MessageBusInterface::class, $serviceClass);
    }
}
