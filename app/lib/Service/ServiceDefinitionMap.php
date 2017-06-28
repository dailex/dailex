<?php

namespace Dailex\Service;

use Daikon\DataStructures\TypedMapTrait;

final class ServiceDefinitionMap
{
    use TypedMapTrait;

    private $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }

    public function getOption($option_key)
    {
        return $this->options->get($option_key);
    }

    public function hasOption($option_key)
    {
        return $this->options->has($option_key);
    }

    private function getItemImplementor()
    {
        return ServiceDefinitionInterface::class;
    }
}
