<?php

namespace DepotBundle\Controller\Admin;

use DepotBundle\Form\Type\Admin\UserEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

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

    public function editAction(Request $request, User $user) {
        $form   = $this->get('form.factory')->create(UserEditType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirectToRoute('users_admin', array('id' => $user->getId()));
        }

        return $this->render('DepotBundle:Admin/Users:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function newAction(Request $request) {
        $user = new User();

        $form = $this->get('form.factory')->create(UserEditType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user->setUsername($user->getEmail());
            $randomFirstPassword = uniqid();
            $user->setPlainPassword($randomFirstPassword);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirectToRoute('users_admin', array('id' => $user->getId()));
        }

        return $this->render('DepotBundle:Admin/Users:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
