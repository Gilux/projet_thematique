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
        $users = $this->getDoctrine()->getRepository("UserBundle:User")->findBy(array('enabled' => 1));
        $users_pending = $this->getDoctrine()->getRepository("UserBundle:User")->findBy(array('enabled' => 0));
        return $this->render('DepotBundle:Admin\Users:index.html.twig',
            array(
                "users" => $users,
                "users_pending" => $users_pending
            )
        );
    }

    public function deleteAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('users_admin');

    }

    public function validateAccountAction(Request $request, User $user)
    {
        $user->setEnabled(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('users_admin');
    }

    public function disableAccountAction(Request $request, User $user)
    {
        $user->setEnabled(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('users_admin');
    }

    public function editAction(Request $request, User $user)
    {
        $form = $this->get('form.factory')->create(UserEditType::class, $user);

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

    public function newAction(Request $request)
    {
        $user = new User();

        $form = $this->get('form.factory')->create(UserEditType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user->setUsername($user->getEmail());
            $randomFirstPassword = uniqid();

            $user->setPlainPassword($randomFirstPassword);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // TODO : Gérer les envois de mails
            $message = (new \Swift_Message('[MIAGE] Vos identifiants du dépôt de devoirs en ligne'))
                ->setFrom([$this->getParameter('dev_mailer_user') => 'Dépôt de devoirs'])
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'Emails/registration.html.twig',
                        array('first_name' => $user->getFirstName(), 'last_name' => $user->getLastName(), 'credentials' => [
                            'login' => $user->getEmail(),
                            'first_password' => $randomFirstPassword
                        ])
                    ),
                    'text/html'
                );
            $retour = $this->get('mailer')->send($message);


            $request->getSession()->getFlashBag()->add('notice', 'Utilisateur bien enregistré.');

            return $this->redirectToRoute('users_admin', array('id' => $user->getId()));
        }

        return $this->render('DepotBundle:Admin/Users:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

