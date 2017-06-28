<?php

namespace Dailex\MessageBus;

use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;

class LazyHandler implements MessageHandlerInterface
{
    /**
     * @var callable
     */
    private $handlerFactory;

    /**
     * @var
     */
    private $compositeHandler;

    /**
     * @param callable $handlerFactory
     */
    public function __construct(callable $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function handle(EnvelopeInterface $envelope): bool
    {
        if (!$this->compositeHandler) {
            $this->compositeHandler = call_user_func($this->handlerFactory);
        }
        return $this->compositeHandler->handle($envelope);
    }
}
