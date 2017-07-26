<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Channel\Subscription\LazySubscription;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportMap;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherList;
use Dailex\Exception\ConfigException;
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
        $settings = $serviceDefinition->getSettings();

        if (!isset($settings['transports'])) {
            throw new ConfigException('Message bus transports configuration is required.');
        }

        $factory = function (
            ServiceLocatorInterface $serviceLocator,
            ConnectorMap $connectorMap
        ) use (
            $injector,
            $settings,
            $serviceClass
        ) {
            $transports = [];
            foreach ($settings['transports'] as $transportName => $transportConfig) {
                $transportClass = $transportConfig['class'];
                $arguments = [':key' => $transportName];
                if (isset($transportConfig['dependencies']['connector'])) {
                    $arguments[':connector'] = $connectorMap->get($transportConfig['dependencies']['connector']);
                }
                $transports[$transportName] = $injector->make($transportClass, $arguments);
            }
            $transportMap = new TransportMap($transports);

            $channelSubs = [
                'commands' => [],
                'commits' => [],
                'events' => []
            ];

            $serviceDefinitionMap = $serviceLocator->getServiceDefinitionMap();
            foreach ($serviceDefinitionMap as $serviceId => $serviceDefinition) {
                foreach ($serviceDefinition->getSubscriptions() as $subscriptionName => $subscriptionConfig) {
                    $channelName = $subscriptionConfig['channel'];
                    $transportName = $subscriptionConfig['transport'];
                    if (!$transportMap->has($transportName)) {
                        throw new ConfigException(
                            sprintf('Message bus transport "%s" has not been configured.', $transportName)
                        );
                    }

                    $channelSubs[$channelName][] = new LazySubscription(
                        $subscriptionName,
                        function () use ($transportMap, $transportName) {
                            return $transportMap->get($transportName);
                        },
                        function () use ($serviceLocator, $serviceId) {
                            return new MessageHandlerList([$serviceLocator->get($serviceId)]);
                        },
                        null,
                        function () use ($injector, $subscriptionConfig) {
                            $enrichers = [];
                            foreach ($subscriptionConfig['enrichers'] ?? [] as $enricherConfig) {
                                $enricherClass = $enricherConfig['class'];
                                $enrichers[] = $injector->make(
                                    $enricherClass,
                                    [':settings' => $enricherConfig['settings'] ?? []]
                                );
                            }
                            return new MetadataEnricherList($enrichers);
                        }
                    );
                }
            }

            $channels = [];
            foreach ($channelSubs as $channelName => $subscriptions) {
                $channels[$channelName] = new Channel($channelName, new SubscriptionMap($subscriptions));
            }
            $channelMap = new ChannelMap($channels);

            return new $serviceClass($channelMap);
        };

        $injector
            ->delegate($serviceClass, $factory)
            ->share($serviceClass)
            ->alias(MessageBusInterface::class, $serviceClass);
    }
}
