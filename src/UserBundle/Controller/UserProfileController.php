<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserProfileController extends Controller
{
    public function indexAction()
    {
        return $this->render('@User/UserProfile/index.html.twig');
    }
}
