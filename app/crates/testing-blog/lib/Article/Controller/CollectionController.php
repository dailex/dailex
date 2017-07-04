<?php

namespace Testing\Blog\Article\Controller;

use Daikon\MessageBus\MessageBusInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Testing\Blog\Article\Domain\Command\CreateArticle;

class CollectionController
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function read(Request $request, Application $app)
    {
        return 'Article collection listing not yet implemented';
    }

    public function write(Request $request, Application $app)
    {
        $this->messageBus->publish(CreateArticle::fromArray([
            'aggregateId' => 'testing.blog.article-123',
            'title' => 'hello world!',
            'content' => 'using cqrs+es to just output this message is over engineered, but it worx :D'
        ]), 'commands');

        return "CreateArticle command was created and dispatched!";
    }
}
