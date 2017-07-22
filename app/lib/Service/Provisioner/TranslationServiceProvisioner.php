<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\EventListener\HttpLocaleListener;
use Dailex\EventListener\SessionLocaleListener;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class TranslationServiceProvisioner implements ProvisionerInterface, EventListenerProviderInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();
        $translationSettings = $configProvider->get('project.translation', []);

        $app->register(new LocaleServiceProvider);
        $app->register(
            new TranslationServiceProvider,
            [
                'locale' => $translationSettings['default_locale'] ?? 'en',
                'locale_fallbacks' => (array)$translationSettings['locale_fallbacks'] ?? ['en']
            ]
        );

        $this->registerResources($app, $configProvider);

        $injector->delegate($serviceClass, function () use ($app) {
            return $app['translator'];
        })->share($serviceClass);

        if (isset($provisionerSettings['alias'])) {
            $alias = $provisionerSettings['alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $serviceClass);
        }
    }

    private function registerResources(Container $app, ConfigProviderInterface $configProvider): void
    {
        $configs = $configProvider->get('translations', []);

        $app->extend('translator.resources', function ($resources, $app) use ($configs) {
            foreach ($configs as $locale => $domains) {
                foreach ($domains as $domain => $translations) {
                    $resources[] = ['array', $translations, $locale, $domain];
                }
            }
            return $resources;
        });
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(
            new HttpLocaleListener($app['translator']->getLocale(), $app['translator']->getFallbackLocales())
        );
        $dispatcher->addSubscriber(new SessionLocaleListener($app['locale']));
    }
}
