<?php

namespace DepotBundle\Controller\Admin;

use DepotBundle\Form\Type\Admin\UserCreateType;
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
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();

            $this->addFlash("success", "L'utilisateur a été supprimé avec succès.");
        } catch(\Doctrine\DBAL\DBALException $e)
        {
            $this->addFlash("error", "Impossible de supprimer cette utilisateur car il est lié à d'autres composants.");
        }

        return $this->redirectToRoute('users_admin');

    }

    public function sendCredentials(User $user, $randomPassword)
    {
        $message = (new \Swift_Message('[MIAGE] Vos identifiants du dépôt de devoirs en ligne'))
            ->setFrom([$this->getParameter('mailer_user') => 'Dépôt de devoirs'])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'Emails/registration.html.twig',
                    array('first_name' => $user->getFirstName(), 'last_name' => $user->getLastName(), 'credentials' => [
                        'login' => $user->getEmail(),
                        'first_password' => $randomPassword
                    ])
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    public function validateAccountAction(Request $request, User $user)
    {
        $user->setEnabled(1);
        if ($user->getLastLogin() == NULL) {
            $randomFirstPassword = uniqid();
            $user->setPlainPassword($randomFirstPassword);
            $this->sendCredentials($user, $randomFirstPassword);
        }
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
        $form = $this->get('form.factory')->create(UserEditType::class, $user, ["user"=>$user]);

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

        $form = $this->get('form.factory')->create(UserCreateType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $if_exist = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $user->getEmail()]);
            if(count($if_exist) == 0) {
                $user->setUsername($user->getEmail());
                $randomFirstPassword = uniqid();

                $user->setPlainPassword($randomFirstPassword);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->sendCredentials($user, $randomFirstPassword);


                $request->getSession()->getFlashBag()->add('notice', 'Utilisateur bien enregistré.');

                return $this->redirectToRoute('users_admin');
            }
            else{
                $this->addFlash("error", "Un utilisateur avec la même adresse email existe déjà.");
                return $this->render('DepotBundle:Admin/Users:new.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }

        return $this->render('DepotBundle:Admin/Users:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

