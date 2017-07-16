<?php

namespace Dailex\Console\Command\Worker;

use Daikon\RabbitMq3\Worker\RabbitMq3Worker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunWorker extends WorkerCommand
{
    protected function configure()
    {
        $this
            ->setName('worker:run')
            ->setDescription('Run an asynchronous job worker.')
            ->addArgument(
                'queue',
                InputArgument::OPTIONAL,
                'Name of the message queue from which to execute jobs.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = $this->serviceLocator->make(
            RabbitMq3Worker::class,
            [
                ':connector' => $this->connectorMap->get('dailex.message_queue'),
                ':settings' => ['queue' => 'testing.blog.article.messages']
            ]
        );
        $worker->run();
    }
}
