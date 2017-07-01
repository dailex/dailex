<?php

namespace Testing\Blog;

use Dailex\Crate\Crate;
use Silex\Application;
use Silex\ControllerCollection;

class BlogCrate extends Crate
{
    public function connect(Application $app)
    {
        $routing = $app['controllers_factory'];

        $crateConfigDir = $this->getConfigDir();
        $appContext = $app['settings']['appContext'];
        $this->loadCrateRoutes($crateConfigDir.'/routing.php', $routing);
        $this->loadCrateRoutes($crateConfigDir."/routing/$appContext.php", $routing);

        return $routing;
    }

    protected function loadCrateRoutes($routesFile, ControllerCollection $routing)
    {
        if (is_readable($routesFile)) {
            require $routesFile;
        }
    }
}
