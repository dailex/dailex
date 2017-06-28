<?php

namespace Dailex\Article\Controller;

use Dailex\Article\Domain\Command\CreateArticle;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ArticleController
{
    public function read(Request $request, Application $app)
    {
        $app['message_bus']->publish(CreateArticle::fromArray([
            'aggregateId' => 'article-123',
            'title' => 'hello world!',
            'content' => 'using cqrs+es to just output this message is over engineered, but it worx :D'
        ]), 'commands');
        return "CreateArticle command was created and dispatched!";
    }
}
