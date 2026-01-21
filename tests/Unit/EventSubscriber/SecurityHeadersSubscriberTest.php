<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\SecurityHeadersSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeadersSubscriberTest extends TestCase
{
    private SecurityHeadersSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new SecurityHeadersSubscriber();
    }

    #[Test]
    public function subscribesToResponseEvent(): void
    {
        $events = SecurityHeadersSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
    }

    #[Test]
    public function addsRestrictiveCspToApiEndpoints(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/api/v1/articles');
        $response = new Response();

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->subscriber->onKernelResponse($event);

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('1; mode=block', $response->headers->get('X-XSS-Protection'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame("default-src 'none'; frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertSame('geolocation=(), microphone=(), camera=()', $response->headers->get('Permissions-Policy'));
    }

    #[Test]
    public function addsPermissiveCspToApiDocEndpoint(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/api/doc');
        $response = new Response();

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->subscriber->onKernelResponse($event);

        $expectedCsp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none'";
        $this->assertSame($expectedCsp, $response->headers->get('Content-Security-Policy'));
    }

    #[Test]
    public function doesNotModifySubRequests(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::SUB_REQUEST,
            $response,
        );

        $this->subscriber->onKernelResponse($event);

        $this->assertNull($response->headers->get('X-Frame-Options'));
    }
}
