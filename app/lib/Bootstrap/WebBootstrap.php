<?php

namespace Dailex\Bootstrap;

use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebBootstrap extends Bootstrap
{
    public function __invoke(Application $app): void
    {
        // start Symfony debug early for web context
        if ($app['debug']) {
            Debug::enable();
        }

        $this->bootstrapConfig($app);
        $this->boostrapServices($app);
        $this->bootstrapSession($app);

        $this->registerTrustedProxies($app);
        $this->registerErrorHandler($app);
        $this->registerViewHandler($app);
    }

    private function boostrapServices(Application $app): void
    {
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->configProvider);
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);
        $app->register(new FormServiceProvider);
        $app->register(new ValidatorServiceProvider);

        if ($app['debug']) {
            $app->register(
                new WebProfilerServiceProvider,
                ['profiler.cache_dir' => $this->configProvider->get('app.cache_dir').'/profiler']
            );
        }
    }

    private function bootstrapSession(Application $app): void
    {
        $app->register(new SessionServiceProvider);
        if ($this->configProvider->get('project.session.auto_start', true)) {
            $app->before(function (Request $request) {
                $request->getSession()->start();
            });
        }
    }

    private function registerTrustedProxies(Application $app): void
    {
        $trustedProxies = $this->configProvider->get('project.framework.trusted_proxies');
        Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
        Request::setTrustedProxies($trustedProxies);
    }

    private function registerErrorHandler(Application $app): void
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app) {
            $message = $e->getMessage();
            //@todo check exception type before getMessageKey()
            $message = $message ?: $e->getMessageKey();
            $errors = ['errors' => ['code' => $code, 'message' => $message]];

            if ($app['debug']) {
                return;
            }

            $templates = [
                'errors/'.$code.'.html.twig',
                'errors/'.substr($code, 0, 2).'x.html.twig',
                'errors/'.substr($code, 0, 1).'xx.html.twig',
                'errors/default.html.twig'
            ];

            return new Response(
                $app['twig']->resolveTemplate($templates)->render($errors),
                $code
            );
        });
    }

    private function registerViewHandler(Application $app): void
    {
        $app->view(function (array $controllerResult, Request $request) use ($app) {
            $view = $this->injector->make($controllerResult[0]);
            return $view->renderHtml($request, $app);
        });
    }
}
