<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute("users_admin");
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')){
            return $this->redirectToRoute("depot_homepage");
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')){
            return $this->redirectToRoute("depot_homepage");
        }
        else {
            return $this->redirectToRoute("fos_user_security_login");
        }
    }
}
