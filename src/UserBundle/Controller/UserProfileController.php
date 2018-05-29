<?php

namespace UserBundle\Controller;

use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserProfileController extends Controller
{
    public function indexAction()
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());

        return $this->render('UserBundle:UserProfile:index.html.twig',
            array(
                "user" => $user
            )
        );
    }

    public function editAction(Request $request)
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());

        $editForm = $this->createForm('UserBundle\Form\Type\UserRegistrationEditType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('UserBundle:UserProfile:edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ));
    }
}
