<?php

namespace Testing\Blog\Article\Controller;

use Daikon\Dbal\Repository\RepositoryMap;
use Daikon\MessageBus\MessageBusInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Testing\Blog\Article\Domain\Command\UpdateArticle;

class ResourceController
{
    private $repositoryMap;

    private $messageBus;

    public function __construct(RepositoryMap $repositoryMap, MessageBusInterface $messageBus)
    {
        $this->repositoryMap = $repositoryMap;
        $this->messageBus = $messageBus;
    }

    public function read(Request $request, Application $app)
    {
        $repository = $this->repositoryMap->get('testing.blog.article.standard');
        $article = $repository->findById($request->attributes->get('articleId'));
        var_dump($article->toArray());
        return 'Article loaded';
    }

    public function write(Request $request, Application $app)
    {
        $this->messageBus->publish(UpdateArticle::fromArray([
            'aggregateId' => $request->attributes->get('articleId'),
            'title' => 'not the same',
            'content' => 'this looks like it updated'
        ]), 'commands');

        return 'UpdateArticle command was created and dispatched!';
    }
}
