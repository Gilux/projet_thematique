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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class LoginListener implements EventSubscriberInterface
{

    private $entityManager;
    private $tokenStorage;
    private $authenticationUtils;
    private $router;
    private $dispatcher;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils, RouterInterface $router, \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationUtils = $authenticationUtils;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
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
        $user = $this->tokenStorage->getToken()->getUser();

        if($user->getLastLogin() == NULL)
        {
            $this->dispatcher->addListener('kernel.response', array($this, 'redirectUser'));
        }
    }

    public function redirectUser(FilterResponseEvent $event)
    {
        $response = new RedirectResponse($this->router->generate('fos_user_change_password'));
        $event->setResponse($response);
    }
}