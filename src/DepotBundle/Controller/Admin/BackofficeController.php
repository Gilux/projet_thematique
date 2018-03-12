<?php

namespace DepotBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BackofficeController extends Controller
{
    public function indexAction()
    {
        $disabledUsers = $this->getDoctrine()->getRepository("UserBundle:User")->findBy(
            array(
                "lastLogin" => null,
                "enabled" => false
            )
        );

        return $this->render('DepotBundle:Admin\Backoffice:index.html.twig',
            array(
                "disabledUsers" => $disabledUsers
            )
        );

    }
}