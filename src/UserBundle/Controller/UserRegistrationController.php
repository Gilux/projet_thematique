<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use UserBundle\Form\Type\UserRegistrationType;

class UserRegistrationController extends Controller
{
    public function demandeAction(Request $request)
    {
        $user = new User();

        $form = $this->get('form.factory')->create(UserRegistrationType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            // Mettre un username et un mot de passe temporaire
            $user->setUsername($user->getEmail());
            $randomFirstPassword = uniqid();
            $user->setPlainPassword($randomFirstPassword);
            $user->addRole($form->get('compte')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'La demande de compte utilisateur a bien été créée.');
        }

        return $this->render('UserBundle:UserRegistration:demande.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
