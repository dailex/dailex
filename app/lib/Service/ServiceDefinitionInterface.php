<?php

namespace Dailex\Service;

interface ServiceDefinitionInterface
{
    public function getProvisioner();

    public function hasProvisioner();

    public function getClass();

    public function hasClass();
}
