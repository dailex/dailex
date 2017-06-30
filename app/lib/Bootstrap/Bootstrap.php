<?php

namespace Dailex\Bootstrap;

use Auryn\Injector;
use Auryn\StandardReflector;
use Daikon\Config\ConfigProvider;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Config\ConfigProviderParams;
use Daikon\Config\YamlConfigLoader;
use Dailex\Config\RoutingConfigLoader;
use Dailex\Controller\ControllerResolverServiceProvider;
use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

abstract class Bootstrap
{
    protected $injector;

    protected $configProvider;

    public function __construct()
    {
        $this->injector = new Injector(new StandardReflector);
    }

    public function __invoke(Application $app, array $settings): Application
    {
        $app['settings'] = $settings;
        $app['version'] = $settings['appVersion'];
        $app['debug'] = $settings['appDebug'];

        $this->bootstrapConfig($app);
        $this->boostrapServices($app);
        $this->bootstrapRouting($app);

        return $app;
    }

    protected function bootstrapConfig(Application $app): void
    {
        $hostPrefix = $app['settings']['hostPrefix'];
        $appContext = $app['settings']['appContext'];
        $appEnv = $app['settings']['appEnv'];
        $configDir = $app['settings']['core']['config_dir'];
        $projectConfigDir = $app['settings']['project']['config_dir'];

        // @todo determine enabled crate config locations

        $loaders = [
            'settings' => [
                'loader' => YamlConfigLoader::class,
                //@todo add host dir
                'locations' => [$configDir, $projectConfigDir],
                'sources' => [
                    'settings.yml',
                    "settings.$appContext.yml",
                    "settings.$appEnv.yml",
                    "settings.$appContext.$appEnv.yml"
                ]
            ],
            'services' => [
                'loader' => YamlConfigLoader::class,
                'locations' => [$configDir, $projectConfigDir],
                'sources' => [
                    'services.yml',
                    "services.$appContext.yml",
                    "services.$appEnv.yml",
                    "services.$appContext.$appEnv.yml"
                ]
            ]
        ];

        // initialize and share the config provider
        $this->configProvider = new ConfigProvider(
            ['app' => ['config' => $app['settings']]],
            new ConfigProviderParams($loaders, 'settings::project')
        );

        $this->injector
            ->share($this->configProvider)
            ->alias(ConfigProviderInterface::CLASS, ConfigProvider::class);
    }

    protected function boostrapServices(Application $app): void
    {
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->configProvider);
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider);
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);
        $app->register(new FormServiceProvider);
        $app->register(new ValidatorServiceProvider);
    }

    protected function bootstrapRouting(Application $app): void
    {
        $hostPrefix = $this->configProvider->get('app::config::hostPrefix');
        $appContext = $this->configProvider->get('app::config::appContext');
        $appEnv = $this->configProvider->get('app::config::appEnv');

        (new RoutingConfigLoader($app))->load(
            //@todo add host dir
            [$this->configProvider->get('app::config::project.config_dir')],
            [
                'routing.php',
                "routing.$appContext.php",
                "routing.$appEnv.php",
                "routing.$appContext.$appEnv.php"
            ]
        );
    }

    protected function bootstrapSession(Application $app): void
    {
        $app->register(new SessionServiceProvider);

        $app->before(function (Request $request) {
            $request->getSession()->start();
        });
    }

    protected function registerTrustedProxies(Application $app, array $trustedProxies): void
    {
        Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
        Request::setTrustedProxies($trustedProxies);
    }
}
