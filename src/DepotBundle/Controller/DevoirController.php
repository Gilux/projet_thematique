<?php

namespace DepotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DevoirController extends Controller
{
    public function indexAction() {
        return $this->render('DepotBundle:Devoir:index.html.twig');
    }

    public function newAction() {
        return $this->render('DepotBundle:Devoir:new.html.twig');
    }
}
