<?php

namespace DepotBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserAdminController extends Controller
{
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository("UserBundle:User")->findAll();
        return $this->render('DepotBundle:Admin\Users:index.html.twig',
            array(
                "users" => $users
            )
        );
    }
}
