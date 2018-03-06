<?php

namespace UserBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginListener implements EventSubscriberInterface
{

    private $entityManager;
    private $tokenStorage;
    private $authenticationUtils;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationUtils = $authenticationUtils;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    public function onSecurityInteractiveLogin( InteractiveLoginEvent $event )
    {
        // TODO here : Faire ce qu'il faut lorsque l'utilisateur est loggé :D
        $user = $this->tokenStorage->getToken()->getUser();
    }
}