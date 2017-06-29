<?php

namespace Dailex\Bootstrap;

use Auryn\Injector;
use Auryn\StandardReflector;
use Daikon\Config\ConfigProvider;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Config\ConfigProviderParams;
use Daikon\Config\YamlConfigLoader;
use Dailex\Config\Loader\RoutingConfigLoader;
use Dailex\Controller\ControllerResolverServiceProvider;
use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class Bootstrap
{
    protected $injector;

    protected $configProvider;

    public function __construct()
    {
        $this->injector = new Injector(new StandardReflector);
    }

    public function __invoke(Application $app, array $settings)
    {
        $this->configProvider = $this->bootstrapConfig($app, $this->injector, $settings);

        $app['version'] = $this->configProvider->get('app::config::appVersion');
        $app['debug'] = $this->configProvider->get('app::config::appDebug');

        $this->bootstrapLogger($app, $this->configProvider, $this->injector);

        // then kick off service provisioning and register some standard service providers.
        $serviceProvisioner = new ServiceProvisioner($app, $this->configProvider, $this->injector);
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider);
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);
        $app->register(new FormServiceProvider);
        $app->register(new ValidatorServiceProvider);

        $this->bootstrapRouting($app, $this->configProvider);

        return $app;
    }

    protected function bootstrapConfig(Application $app, Injector $injector, array $settings)
    {
        $app['settings'] = $settings;
        $hostPrefix = $settings['hostPrefix'];
        $appContext = $settings['appContext'];
        $appEnv = $settings['appEnv'];
        $configDir = $settings['core']['config_dir'].DIRECTORY_SEPARATOR;
        $projectConfigDir = $settings['project']['config_dir'].DIRECTORY_SEPARATOR;

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
        $config = new ConfigProvider(
            ['app' => ['config' => $settings]],
            new ConfigProviderParams($loaders, 'settings::project')
        );

        $injector->share($config)->alias(ConfigProviderInterface::CLASS, get_class($config));

        return $config;
    }

    protected function bootstrapLogger(Application $app, ConfigProviderInterface $configProvider, Injector $injector)
    {
        // register logger as first item within the DI chain
        // @todo log rotation
        $app->register(new MonologServiceProvider, [
            'monolog.logfile' => $configProvider->get('app::config::project.log_dir').'/dailex.log'
        ]);

        $logger = $app['logger'];

        $injector->share($logger)->alias(LoggerInterface::class, get_class($logger));

        return $logger;
    }

    protected function bootstrapRouting(Application $app, ConfigProviderInterface $configProvider)
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

    protected function bootstrapSession(Application $app)
    {
        // sessions are started explicitly when required
        $app->register(new SessionServiceProvider);

        $app->before(function (Request $request) {
            $request->getSession()->start();
        });
    }

    protected function registerTrustedProxies(Application $app, array $trustedProxies)
    {
        Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
        Request::setTrustedProxies($trustedProxies);
    }
}
