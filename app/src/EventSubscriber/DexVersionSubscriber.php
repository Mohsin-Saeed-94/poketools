<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Event Subscriber to resolve the Version from the request.
 */
class DexVersionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * DexVersionSubscriber constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => ['onKernelController', -10],
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequest()->attributes->has('version')) {
            $this->container->set('app.active_version', $event->getRequest()->attributes->get('version'));
        }
    }
}
