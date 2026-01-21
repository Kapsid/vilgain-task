<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestLoggingSubscriber implements EventSubscriberInterface
{
    /** @var array<int, float> */
    private array $requestStartTimes = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255],
            KernelEvents::RESPONSE => ['onKernelResponse', -255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->requestStartTimes[spl_object_id($request)] = microtime(true);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $requestId = spl_object_id($request);

        $durationMs = isset($this->requestStartTimes[$requestId])
            ? round((microtime(true) - $this->requestStartTimes[$requestId]) * 1000, 2)
            : null;

        unset($this->requestStartTimes[$requestId]);

        $this->logger->info(\sprintf(
            '%s %s %d %sms',
            $request->getMethod(),
            $request->getPathInfo(),
            $response->getStatusCode(),
            $durationMs,
        ));
    }
}
