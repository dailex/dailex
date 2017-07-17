<?php

namespace Dailex\Service;

use Assert\Assertion;
use Daikon\AsyncJob\Event\JobFailed;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Psr\Log\LoggerInterface;

final class LoggingService implements LoggerInterface, MessageHandlerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->logger->log($message, $context);
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        $message = $envelope->getMessage();
        $metadata = $envelope->getMetadata();
        Assertion::isInstanceOf($message, JobFailed::class);

        //@todo improve trace output
        $this->logger->error(
            'Message failed to be handled with error "'.$metadata->get('_error_message').'".',
            [
                'message' => print_r($message->toArray(), true),
                'metadata' => print_r($envelope->getMetadata()->toArray(), true),
            ]
        );

        return true;
    }
}
