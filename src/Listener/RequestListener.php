<?php

namespace App\Listener;

use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(RouterInterface $router, UserRepository $userRepository)
    {
        $this->router = $router;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMasterRequest() && ($this->userRepository->countAll() === 0)) {
            $route = $event->getRequest()->get('_route');
            if ($route && $route[0] !== '_' && $route !== 'config') {
                $event->setResponse(
                    new RedirectResponse($this->router->generate('config'))
                );
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
