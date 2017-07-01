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

        $this->registerTwig($app, $injector, $configProvider);

        $injector
            ->share($serviceClass)
            ->alias(TemplateRendererInterface::CLASS, $serviceClass)
            ->delegate(
                $serviceClass,
                function (Filesystem $filesystem) use ($serviceClass, $app) {
                    return new $serviceClass($app['twig'], $filesystem);
                }
            );
    }

    private function registerTwig(Container $app, Injector $injector, ConfigProviderInterface $configProvider)
    {
        $dailexDir = $configProvider->get('app.dailex.dir');
        $appDir = $configProvider->get('app.dir');

        $app->register(new TwigServiceProvider);

        $namespacedPaths = $this->getCrateTemplatesPaths($configProvider);
        $projectTemplates = $appDir.'/app/templates';
        $namespacedPaths['dailex'][] = $dailexDir.'/app/templates';
        $namespacedPaths['project'][] = $projectTemplates;
//         if ($hostPrefix = $configProvider->getHostPrefix()) {
//             $projectHostTemplates = $projectTemplates.'/'.$hostPrefix;
//             if (is_readable($projectHostTemplates)) {
//                 $namespacedPaths['project'][] = $projectHostTemplates;
//             }
//         }

        $app['twig.form.templates'] = [ 'bootstrap_3_layout.html.twig' ];
        $app['twig.options'] = [ 'cache' => $configProvider->get('app.cache_dir').'/twig' ];
        $app['twig.loader.filesystem'] = function () use ($namespacedPaths, $projectTemplates) {
            $filesystem = new \Twig_Loader_Filesystem($projectTemplates);
            foreach ($namespacedPaths as $namespace => $path) {
                $filesystem->setPaths($path, $namespace);
            }
            return $filesystem;
        };

        $settings = $configProvider->get('services.dailex.infrastructure.template_renderer.provisioner.settings');
        $app['twig'] = $app->extend('twig', function ($twig, $app) use ($injector, $settings) {
            foreach ($settings['extensions'] ?? [] as $extension) {
                $twig->addExtension($injector->make($extension));
            }
            return $twig;
        });
    }

    protected function getCrateTemplatesPaths(ConfigProviderInterface $configProvider)
    {
        $appDir = $configProvider->get('app.dir').'/app/templates';

        $paths = [];
//         foreach ($configProvider->getCrateMap() as $crate) {
//             $cratePrefix = $crate->getPrefix('-');
//             $projectCratePath = $projectDir.'/'.$cratePrefix;
//             if (is_readable($projectCratePath)) {
//                 $paths[$cratePrefix][] = $projectDir.'/'.$cratePrefix;
//             }
//             $templatesPath = $crate->getRootDir().'/templates';
//             if (is_readable($templatesPath)) {
//                 $paths[$cratePrefix][] = $templatesPath;
//             }
//         }

        return $paths;
    }
}
