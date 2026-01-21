<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $environment = 'prod',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $data = $this->buildErrorResponse($exception);
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = $exception instanceof HttpExceptionInterface
            ? $exception->getHeaders()
            : [];

        $response = new JsonResponse($data, $statusCode, $headers);
        $event->setResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildErrorResponse(Throwable $exception): array
    {
        // Check for validation errors in exception chain
        $validationException = $this->findValidationException($exception);
        if (null !== $validationException) {
            return $this->formatValidationErrors($validationException);
        }

        // HTTP exceptions - return their message
        if ($exception instanceof HttpExceptionInterface) {
            return [
                'error' => $exception->getMessage(),
                'code' => $exception->getStatusCode(),
            ];
        }

        // Non-HTTP exceptions - hide details in production
        if ('dev' === $this->environment || 'test' === $this->environment) {
            return [
                'error' => $exception->getMessage(),
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'exception' => $exception::class,
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return [
            'error' => 'An unexpected error occurred.',
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];
    }

    private function findValidationException(Throwable $exception): ?ValidationFailedException
    {
        $current = $exception;
        while (null !== $current) {
            if ($current instanceof ValidationFailedException) {
                return $current;
            }
            $current = $current->getPrevious();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatValidationErrors(ValidationFailedException $exception): array
    {
        $errors = [];
        $violations = $exception->getViolations();

        foreach ($violations as $violation) {
            $field = $violation->getPropertyPath();
            $errors[$field][] = $violation->getMessage();
        }

        return [
            'error' => 'Validation failed',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'violations' => $errors,
        ];
    }
}
