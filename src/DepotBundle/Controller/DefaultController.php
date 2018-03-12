<?php

namespace DepotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());

        return $this->render('DepotBundle:Default:index.html.twig', array("user"=>$user));
    }
}
