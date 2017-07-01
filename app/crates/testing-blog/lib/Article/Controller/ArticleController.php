<?php

namespace Testing\Blog\Article\Controller;

use Daikon\MessageBus\MessageBusInterface;
use Dailex\Article\Domain\Command\CreateArticle;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ArticleController
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function read(Request $request, Application $app)
    {
        $this->messageBus->publish(CreateArticle::fromArray([
            'aggregateId' => 'article-123',
            'title' => 'hello world!',
            'content' => 'using cqrs+es to just output this message is over engineered, but it worx :D'
        ]), 'commands');

        return "CreateArticle command was created and dispatched!";
    }
}
