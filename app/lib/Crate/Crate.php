<?php

namespace Dailex\Crate;

final class Crate implements CrateInterface
{
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getLocation(): string
    {
        return $this->settings['location'];
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
