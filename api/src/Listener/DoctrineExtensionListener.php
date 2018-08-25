<?php

namespace App\Listener;

use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DoctrineExtensionListener
{

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var TranslatableListener
     */
    protected $translatable;

    /**
     * @var LoggableListener
     */
    protected $loggable;

    /**
     * @var BlameableListener
     */
    protected $blameable;

    /**
     * DoctrineExtensionListener constructor.
     *
     * @param TranslatorInterface           $translator
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param TranslatableListener          $translatable
     * @param LoggableListener              $loggable
     * @param BlameableListener             $blameable
     */
    public function __construct(
      TranslatorInterface $translator,
      TokenStorageInterface $tokenStorage,
      AuthorizationCheckerInterface $authChecker,
      TranslatableListener $translatable,
      LoggableListener $loggable,
      BlameableListener $blameable
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->translatable = $translatable;
        $this->loggable = $loggable;
        $this->blameable = $blameable;
    }

    public function onLateKernelRequest(
      GetResponseEvent $event
    ) {
        $this->translatable->setTranslatableLocale(
          $event->getRequest()->getLocale()
        );
    }

    public function onConsoleCommand()
    {
        $this->translatable->setTranslatableLocale(
          $this->translator->getLocale()
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $tokenStorage = $this->tokenStorage->getToken();
        if (null !== $tokenStorage && $this->authChecker->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
          )) {
            # for loggable behavior
            $this->loggable->setUsername($tokenStorage->getUser());

            # for blameable behavior
            $this->blameable->setUserValue($tokenStorage->getUser());
        }

    }
}
