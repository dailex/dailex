<?php

namespace Dailex\Console\Command\Worker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Daikon\RabbitMq3\Job\RabbitMq3Worker;

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
        $worker = $this->workerMap->get('dailex.message_queue');
        $worker->run(['queue' => 'testing.blog.article.messages']);
    }
}
