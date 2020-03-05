<?php

namespace App\EventSubscriber;

use App\Entity\Version;
use App\Repository\VersionRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

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
     * @var Version
     */
    private $defaultVersion;

    /**
     * DexVersionSubscriber constructor.
     *
     * @param ContainerInterface $container
     * @param VersionRepository $versionRepo
     * @param string $defaultVersionSlug
     */
    public function __construct(
        ContainerInterface $container,
        VersionRepository $versionRepo,
        string $defaultVersionSlug
    ) {
        $this->container = $container;
        $this->defaultVersion = $versionRepo->findOneBy(['slug' => $defaultVersionSlug]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['onKernelRequest', 0],
            'kernel.controller' => ['onKernelController', -10],
        ];
    }

    /**
     * Event handler for kernel.request
     *
     * Makes the default version entity available everywhere.  It will be replaced
     * later if a version is defined in the route.
     */
    public function onKernelRequest(): void
    {
        $this->container->set('app.active_version', $this->defaultVersion);
    }

    /**
     * Event handler for kernel.controller
     *
     * Makes the resolved version available everywhere.
     *
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->getRequest()->attributes->has('version')) {
            $this->container->set('app.active_version', $event->getRequest()->attributes->get('version'));
        }
    }
}
