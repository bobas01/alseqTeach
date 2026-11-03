<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\DomainException;
use App\Service\AdminAuth;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(private AdminAuth $auth)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 8]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/api/admin')) {
            return;
        }
        if ($request->isMethod('OPTIONS') || ($path === '/api/admin/login' && $request->isMethod('POST'))) {
            return;
        }
        if (!$this->auth->isValid($request->headers->get('X-Admin-Token'))) {
            throw new DomainException('Não autorizado.', 401, 'unauthorized');
        }
    }
}
