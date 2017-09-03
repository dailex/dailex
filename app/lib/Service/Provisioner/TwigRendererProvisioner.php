<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Renderer\TemplateRendererInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Filesystem\Filesystem;

final class TwigRendererProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $settings = $serviceDefinition->getSettings();

        $app->register(new TwigServiceProvider);

        $dailexDir = $configProvider->get('app.dailex.dir');
        $appDir = $configProvider->get('app.dir');
        $namespacedPaths = $this->getCrateTemplatesPaths($configProvider);
        $projectTemplates = $appDir.'/templates';
        $namespacedPaths['dailex'][] = $dailexDir.'/app/templates';
        $namespacedPaths['project'][] = $projectTemplates;

        $app['twig.form.templates'] = ['bootstrap_3_layout.html.twig'];
        $app['twig.options'] = [ 'cache' => $configProvider->get('app.cache_dir').'/twig' ];
        $app['twig.loader.filesystem'] = function () use ($namespacedPaths, $projectTemplates) {
            $filesystem = new \Twig_Loader_Filesystem($projectTemplates);
            foreach ($namespacedPaths as $namespace => $path) {
                $filesystem->setPaths($path, $namespace);
            }
            return $filesystem;
        };

        $app['twig'] = $app->extend('twig', function ($twig, $app) use ($injector, $settings) {
            foreach ($settings['extensions'] ?? [] as $extension) {
                $twig->addExtension($injector->make($extension));
            }
            return $twig;
        });

        $injector
            ->delegate(
                $serviceClass,
                function (Filesystem $filesystem) use ($serviceClass, $app) {
                    return new $serviceClass($app['twig'], $filesystem);
                }
            )
            ->share($serviceClass)
            ->alias(TemplateRendererInterface::class, $serviceClass);
    }

    private function getCrateTemplatesPaths(ConfigProviderInterface $configProvider)
    {
        $paths = [];
        foreach ($configProvider->get('crates') as $crateName => $crateConfig) {
            $templatesPath = $crateConfig['template_dir'] ?? null;
            if (is_readable($templatesPath)) {
                $paths[$crateName][] = $templatesPath;
            }
        }
        return $paths;
    }
}
