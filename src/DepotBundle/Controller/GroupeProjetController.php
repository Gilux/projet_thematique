<?php
/**
 * Created by PhpStorm.
 * User: vincentpochon
 * Date: 10/05/2018
 * Time: 10:30
 */

namespace DepotBundle\Controller;


use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\Groupe_Devoir;
use DepotBundle\Entity\Groupe_projet;
use DepotBundle\Entity\UserGroupeProjet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class GroupeProjetController extends Controller
{
    public function newAction(Request $request, Groupe $groupe, Devoir $devoir)
    {
        $flag = false;
        $groupes_projets = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["devoir" => $devoir]);
        foreach ($groupes_projets as $groupes_projet) {
            $users_groupes_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupes_projet]);
            foreach ($users_groupes_projets as $users_groupes_projet) {
                if ($this->getUser()->getId() == $users_groupes_projet->getUser()->getId()) {
                    $flag = true;
                }
            }
        }

        if ($flag == true) {
            $this->addFlash("error", "L'utilisateur appartient déjà à un groupe");
        } else {
            $groupeProjet = new Groupe_projet();
            $groupe_devoir = $this->getDoctrine()->getRepository(Groupe_Devoir::class)->findOneBy(["groupe" => $groupe, "devoir" => $devoir]);
            $groupeProjet->setGroupeDevoir($groupe_devoir);
            $groupeProjet->setDevoir($devoir);
            $groupeProjet->setGroupe($groupe);
            
            $groupeProjet->setName($this->getUser()->getLastName() . $devoir->getId());

            $em = $this->getDoctrine()->getManager();
            $em->persist($groupeProjet);
            $em->flush();

            $userGroupeProjet = new UserGroupeProjet();
            $userGroupeProjet->setUser($this->getUser());
            $userGroupeProjet->setGroupeProjet($groupeProjet);
            $userGroupeProjet->setStatus(1);
            $userGroupeProjet->setLeader(1);

            $em->persist($userGroupeProjet);
            $em->flush();
        }

        return $this->redirectToRoute("show_devoir", ["devoir" => $devoir->getId()]);
    }

    public function leaveAction(Request $request, Groupe_projet $groupe_projet)
    {
        $users_groupes_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupe_projet]);
        $user_groupe_projet = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findOneBy(["groupe_projet" => $groupe_projet, "user" => $this->getUser()]);
        $devoir = $groupe_projet->getDevoir();

        $leader = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupe_projet, "leader" => 1])[0]->getUser();

        $em = $this->getDoctrine()->getManager();

        if ($leader == true) {
            if (count($users_groupes_projets) != 1) {
                $this->addFlash("error", "Vous ne pouvez pas quitter le groupe, des personnes y sont présente.");
            } else {
                $em->remove($user_groupe_projet);
                $em->flush();

                $em->remove($groupe_projet);
                $em->flush();
            }
        } else {
            $em->remove($user_groupe_projet);
            $em->flush();
        }

        return $this->redirectToRoute("show_devoir", ["devoir" => $devoir->getId()]);
    }

    public function sendNotification(User $leader, User $user, Groupe_projet $groupe_projet)
    {

        $devoir = $groupe_projet->getDevoir();
        $ueName = $devoir->getUE()->getNom();

        //todo récupérer le groupe devoir correspondant pour avoir le groupeName et la date à rendre

        $message = (new \Swift_Message('[MIAGE] Vous avez une nouvelle demande de  ' . $user->getUsername() . '  pour rejoindre votre groupe du devoir: ' . $devoir->getTitre() . ''))
            ->setFrom([$this->getParameter('mailer_user') => 'Dépôt de devoirs'])
            ->setTo($leader->getEmail())
            ->setBody(
                $this->renderView(
                    'Emails/demande_groupe_projet.html.twig',
                    array(
                        'first_name' => $leader->getFirstName(),
                        'last_name' => $leader->getLastName(),
                        'user_request' => [
                            'first_name' => $user->getFirstName(),
                            'last_name' => $user->getLastName(),
                            'username' => $user->getUsername(),
                        ],
                        'ue' => $ueName,
                        'devoir' => [
                            'titre' => $devoir->getTitre(),
                        ]
                    )
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        //notifications
        $manager = $this->get('mgilet.notification');
        $notif = $manager->createNotification('Demande de ' . $user->getUsername() . ' pour rejoindre le groupe du devoir');
        $notif->setMessage($ueName . '/' . $devoir->getTitre() . '');
        //fixme get real link
        $notif->setLink('http://symfony.com/');
        $manager->addNotification(array($leader), $notif, true);
    }

    public function joinAction(Request $request, Groupe_projet $groupe_projet)
    {
        $em = $this->getDoctrine()->getManager();

        //todo regarder si l'utilisateur n'appartient pas déjà au groupe projet


        //On ajoute l'utilisateur au groupe projet
        $userGroupeProjet = new UserGroupeProjet();
        $userGroupeProjet->setUser($this->getUser());
        $userGroupeProjet->setGroupeProjet($groupe_projet);
        $userGroupeProjet->setStatus(0);
        $userGroupeProjet->setLeader(0);

        $em->persist($userGroupeProjet);
        $em->flush();

        //On récupère le leader du groupe
        $leader = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupe_projet, "leader" => 1])[0]->getUser();

        //Envoie d'une notification au leader
        $this->sendNotification($leader, $this->getUser(), $groupe_projet);

        return $this->redirectToRoute("show_devoir", ["devoir" => $groupe_projet->getDevoir()->getId()]);

    }

    public function acceptAction(Request $request, Groupe_projet $groupe_projet, UserGroupeProjet $user_groupe_projet)
    {

        $em = $this->getDoctrine()->getManager();
        $user_groupe_projet->setStatus(1);
        $em->persist($user_groupe_projet);
        $em->flush();
        $this->sendNotificationToUser($user_groupe_projet->getUser(), $groupe_projet, 'accept');
        return $this->redirectToRoute("show_devoir", ["devoir" => $groupe_projet->getDevoir()->getId()]);
    }

    public function declineAction(Request $request, Groupe_projet $groupe_projet, UserGroupeProjet $user_groupe_projet)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user_groupe_projet);
        $em->flush();
        $this->sendNotificationToUser($user_groupe_projet->getUser(), $groupe_projet, 'decline');
        return $this->redirectToRoute("show_devoir", ["devoir" => $groupe_projet->getDevoir()->getId()]);
    }

    public function sendNotificationToUser(User $user, Groupe_projet $groupe_projet, $decision)
    {
        //On récupère le leader du groupe
        $leader = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupe_projet, "leader" => 1])[0]->getUser();

        if ($decision == 'accept') {
            $title = '[MIAGE] Votre demande a été accepté par ' . $leader->getUsername() . ' pour rejoindre le groupe du devoir : ' . $groupe_projet->getDevoir()->getTitre();
            $messageNotification = 'Votre demande a été accepté par ' . $leader->getUsername();
        } else {
            $title = '[MIAGE] Vous demande a été décliné par ' . $leader->getUsername() . ' pour rejoindre le groupe du devoir : ' . $groupe_projet->getDevoir()->getTitre();
            $messageNotification = 'Votre demande a été refusée par ' . $leader->getUsername();
        }
        $devoir = $groupe_projet->getDevoir();
        $ueName = $devoir->getUE()->getNom();

        //mail
        $message = (new \Swift_Message($title))
            ->setFrom([$this->getParameter('mailer_user') => 'Dépôt de devoirs'])
            ->setTo($leader->getEmail())
            ->setBody(
                $this->renderView(
                    'Emails/decision_groupe_projet.html.twig',
                    array(
                        'decision' => $decision,
                        'first_name' => $user->getFirstName(),
                        'last_name' => $user->getLastName(),
                        'leader' => [
                            'first_name' => $leader->getFirstName(),
                            'last_name' => $leader->getLastName(),
                            'username' => $leader->getUsername(),
                        ],
                        'ue' => $ueName,
                        'devoir' => [
                            'titre' => $devoir->getTitre(),
                        ]
                    )
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        //notifications
        $manager = $this->get('mgilet.notification');
        $notif = $manager->createNotification('Modération de votre demande pour rejoindre le groupe du devoir : ' . $devoir->getTitre());
        $notif->setMessage($messageNotification);
        //fixme get real link
        $notif->setLink('http://symfony.com/');
        $manager->addNotification(array($user), $notif, true);
    }
}