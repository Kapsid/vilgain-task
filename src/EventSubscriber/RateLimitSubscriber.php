<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\TooManyRequestsException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $registrationLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $clientIp = $request->getClientIp() ?? 'unknown';

        $limiter = match ($route) {
            'auth_login' => $this->loginLimiter->create($clientIp),
            'auth_register' => $this->registrationLimiter->create($clientIp),
            default => null,
        };

        if (null === $limiter) {
            return;
        }

        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsException($limit->getRetryAfter()->getTimestamp() - time());
        }
    }
}
