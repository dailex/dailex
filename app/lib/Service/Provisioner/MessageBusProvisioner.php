<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageBus;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
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

        if (!isset($provisionerSettings['channels'])) {
            throw new ConfigException('Message bus channel configuration is required.');
        }

        $channels = [];
        foreach ($provisionerSettings['channels'] as $channelName) {
            $channels[] = new Channel($channelName, new SubscriptionMap);
        }

        $callback = function (MessageBusInterface $messageBus) use ($injector, $provisionerSettings) {
            $this->prepareMessageBus($messageBus, $injector);
        };

        $injector
            ->define($serviceClass, [':channelMap' => new ChannelMap($channels)])
            ->prepare($serviceClass, $callback)
            ->share($serviceClass)
            ->alias(MessageBusInterface::class, $serviceClass);
    }
}
