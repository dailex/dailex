<?php

namespace Dailex\Bootstrap;

use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

final class ApiBootstrap extends Bootstrap
{
    public function __invoke(Application $app): void
    {
        $this->bootstrapConfig($app);
        $this->boostrapServices($app);

        $this->registerTrustedProxies($app);
        $this->registerErrorHandler($app);
        $this->registerViewHandler($app);
    }

    private function boostrapServices(Application $app): void
    {
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->configProvider);
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ValidatorServiceProvider);
    }

    private function registerErrorHandler(Application $app): void
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app, $injector) {
            $translator = $this->injector->make(TranslatorInterface::class);
            $message = $e->getMessage();
            if (empty($message) && is_callable([$e, 'getMessageKey'])) {
                $message = $e->getMessageKey();
            }
            $errors = [
                'errors' => [
                    'code' => $code,
                    'message' => $translator->trans($message, [], 'errors')
                ]
            ];

            return new JsonResponse($errors, $code);
        });
    }

    private function registerViewHandler(Application $app): void
    {
        $app->view(function (array $controllerResult, Request $request) use ($app, $injector) {
            $view = $this->injector->make($controllerResult[0]);
            return $view->renderJson($request, $app);
        });
    }

    private function registerTrustedProxies(Application $app): void
    {
        $trustedProxies = $this->configProvider->get('project.framework.trusted_proxies');
        Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
        Request::setTrustedProxies($trustedProxies);
    }
}
