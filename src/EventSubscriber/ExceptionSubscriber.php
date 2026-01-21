<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof HttpExceptionInterface) {
            return;
        }

        $data = $this->buildErrorResponse($exception);

        $response = new JsonResponse(
            $data,
            $exception->getStatusCode(),
            $exception->getHeaders(),
        );

        $event->setResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildErrorResponse(HttpExceptionInterface $exception): array
    {
        if ($exception instanceof UnprocessableEntityHttpException) {
            $previous = $exception->getPrevious();
            if ($previous instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
                $violations = $previous->getPrevious();
                if ($violations instanceof ConstraintViolationListInterface) {
                    return $this->formatValidationErrors($violations);
                }
            }
        }

        return [
            'error' => $exception->getMessage(),
            'code' => $exception->getStatusCode(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

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
