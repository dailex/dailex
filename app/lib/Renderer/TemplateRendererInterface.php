<?php

namespace Dailex\Renderer;

interface TemplateRendererInterface
{
    public function render($template, array $data = [], array $settings = []);

    public function renderToString($template, array $data = [], array $settings = []);

    public function renderToFile($template, $target_location, array $data = [], array $settings = []);
}
