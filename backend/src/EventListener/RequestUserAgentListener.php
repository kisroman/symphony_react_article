<?php
declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestUserAgentListener
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(private readonly LoggerInterface $logger) {

    }

    /**
     * @param RequestEvent $event
     *
     * @return void
     */
    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');

        $this->logger->info(sprintf('User-Agent: %s', $userAgent));
    }
}
