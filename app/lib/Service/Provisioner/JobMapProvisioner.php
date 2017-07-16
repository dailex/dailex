<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\AsyncJob\Job\Job;
use Daikon\AsyncJob\Job\JobMap;
use Daikon\AsyncJob\Strategy\JobStrategyMap;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class JobMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $strategyConfigs = $configProvider->get('jobs.job_strategies', []);
        $jobConfigs = $configProvider->get('jobs.jobs', []);

        $this->delegateJobStrategyMap($injector, $strategyConfigs);
        $this->delegateJobMap($injector, $jobConfigs);
    }

    private function delegateJobMap(Injector $injector, array $jobConfigs)
    {
        $factory = function (JobStrategyMap $strategyMap) use ($injector, $jobConfigs) {
            $jobs = [];
            foreach ($jobConfigs as $jobName => $jobConfig) {
                $jobs[$jobName] = $injector->make(
                    Job::class,
                    [
                        ':jobStrategy' => $strategyMap->get($jobConfig['job_strategy']),
                        ':settings' => $jobConfig['settings'] ?? []
                    ]
                );
            }
            return new JobMap($jobs);
        };

        $injector->share(JobMap::class)->delegate(JobMap::class, $factory);
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
}
