<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ApiExceptionListener
{
    /**
     * @param ExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = (string) $request->attributes->get('_route');

        if ($routeName === '' || !str_starts_with($routeName, 'api_')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $event->setResponse(
                new JsonResponse(['message' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST)
            );

            return;
        }

        $event->setResponse(
            new JsonResponse(['message' => 'Something went wrong'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
        );
    }
}
