<?php

namespace DepotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());
        return $this->render('DepotBundle:Default:index.html.twig', array('user' => $user));
    }

    public function getNotifications()
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());
        $manager = $this->get('mgilet.notification');
        $entity = $manager->getNotifiableEntity($user);
        $interface = $manager->getNotifiableInterface($entity);
        return $this->render(
            'notification/notifications.html.twig',
            array("notifiableInterface" => $interface, "notifiableNotifications" => $entity->getnotifiableNotifications(), "user" => $user)
        );
    }
}
