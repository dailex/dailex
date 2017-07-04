<?php

namespace Testing\Blog\Article\Controller;

use Daikon\MessageBus\MessageBusInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Testing\Blog\Article\Domain\Command\UpdateArticle;

class ResourceController
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function write(Request $request, Application $app)
    {
        $this->messageBus->publish(UpdateArticle::fromArray([
            'aggregateId' => $request->attributes->get('articleId'),
            'title' => 'not the same',
            'content' => 'this looks like it updated'
        ]), 'commands');

        return "UpdateArticle command was created and dispatched!";
    }
}
