<?php

namespace DepotBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BackofficeController extends Controller
{
    public function indexAction()
    {
        return $this->render('DepotBundle:Admin\Backoffice:index.html.twig');
    }
}