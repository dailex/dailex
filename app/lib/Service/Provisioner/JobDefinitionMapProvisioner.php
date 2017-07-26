<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\AsyncJob\Job\JobDefinitionMap;
use Daikon\AsyncJob\Strategy\JobStrategyMap;
use Daikon\AsyncJob\Worker\WorkerMap;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class JobDefinitionMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $workerConfigs = $configProvider->get('jobs.job_workers', []);
        $strategyConfigs = $configProvider->get('jobs.job_strategies', []);
        $jobConfigs = $configProvider->get('jobs.jobs', []);

        $this->delegateJobStrategyMap($injector, $strategyConfigs);
        $this->delegateJobDefinitionMap($injector, $jobConfigs);
        $this->delegateWorkerMap($injector, $workerConfigs);
    }

    private function delegateJobDefinitionMap(Injector $injector, array $jobConfigs)
    {
        $factory = function (JobStrategyMap $strategyMap) use ($injector, $jobConfigs) {
            $jobs = [];
            foreach ($jobConfigs as $jobName => $jobConfig) {
                $jobs[$jobName] = $injector->make(
                    $jobConfig['class'],
                    [
                        ':jobStrategy' => $strategyMap->get($jobConfig['job_strategy']),
                        ':settings' => $jobConfig['settings'] ?? []
                    ]
                );
            }
            return new JobDefinitionMap($jobs);
        };

        $injector->share(JobDefinitionMap::class)->delegate(JobDefinitionMap::class, $factory);
    }

    private function delegateJobStrategyMap(Injector $injector, array $strategyConfigs)
    {
        $factory = function () use ($injector, $strategyConfigs) {
            $strategies = [];
            foreach ($strategyConfigs as $strategyName => $strategyConfig) {
                $strategies[$strategyName] = $injector->make(
                    $strategyConfig['class'],
                    [
                        ':retryStrategy' => $injector->make(
                            $strategyConfig['retry']['class'],
                            [':settings' => $strategyConfig['retry']['settings'] ?? []]
                        ),
                        ':failureStrategy' => $injector->make(
                            $strategyConfig['failure']['class'],
                            [':settings' => $strategyConfig['failure']['settings'] ?? []]
                        )
                    ]
                );
            }
            return new JobStrategyMap($strategies);
        };

        $injector->share(JobStrategyMap::class)->delegate(JobStrategyMap::class, $factory);
    }

    private function delegateWorkerMap(Injector $injector, array $workerConfigs)
    {
        $factory = function (
            ConnectorMap $connectorMap,
            JobDefinitionMap $jobDefinitionMap
        ) use (
            $injector,
            $workerConfigs
        ) {
            $workers = [];
            foreach ($workerConfigs as $workerName => $workerConfig) {
                $workers[$workerName] = $injector->make(
                    $workerConfig['class'],
                    [
                        ':connector' => $connectorMap->get($workerConfig['dependencies']['connector']),
                        ':jobDefinitionMap' => $jobDefinitionMap,
                        ':settings' => $workerConfig['settings'] ?? []
                    ]
                );
            }
            return new WorkerMap($workers);
        };

        $injector->share(WorkerMap::class)->delegate(WorkerMap::class, $factory);
    }
}
