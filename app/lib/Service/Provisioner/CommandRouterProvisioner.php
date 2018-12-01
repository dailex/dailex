<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Infrastructure\UnitOfWorkMap;
use Dailex\Service\CommandRouter;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class CommandRouterProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $injector
            ->share(CommandRouter::class)
            ->delegate(
                CommandRouter::class,
                $this->factory(
                    $injector,
                    $configProvider->get('services.dailex.command_router.commands', [])
                )
            );
    }

    private function factory(Injector $injector, array $cmdRoutingConfig): callable
    {
        return function (UnitOfWorkMap $uowMap) use ($injector, $cmdRoutingConfig): CommandRouter {
            $handlerMap = [];
            foreach ($cmdRoutingConfig as $uowKey => $handlerMap) {
                foreach ($handlerMap as $commandFqcn => $handlerFqcn) {
                    $handlerMap[$commandFqcn] = function () use ($injector, $handlerFqcn, $uowMap, $uowKey) {
                        return $injector->make(
                            $handlerFqcn,
                            [ ':unitOfWork' => $uowMap->get($uowKey) ]
                        );
                    };
                }
            }
            return new CommandRouter($handlerMap);
        };
    }
}
