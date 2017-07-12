<?php

namespace Dailex\Crate;

interface CrateInterface
{
    public function getLocation(): string;

    public function getSettings(): array;
}
