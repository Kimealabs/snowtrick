<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AccessDeniedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $message = "Access Denied";
        if ($exception->getAttributes()[0] == 'only_not_connected') {
            $message = "Only not connected ressource, Please logout !";
        }
        if ($exception->getAttributes()[0] == 'only_connected_confirmed') {
            $message = "Only for Confirmed Account !";
        }
        if ($exception->getAttributes()[0] == 'only_connected_not_confirmed') {
            $message = "Only for Not Confirmed Account !";
        }
        if ($exception->getAttributes()[0] == 'connected') {
            $message = "Only logged User ressource !";
        }


        $session = $event->getRequest()->getSession();
        $session->getFlashBag()->add('warning', $message);
        $event->setResponse(new RedirectResponse('./'));
        $event->stopPropagation();
    }
}
