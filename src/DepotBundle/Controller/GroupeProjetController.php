<?php
/**
 * Created by PhpStorm.
 * User: vincentpochon
 * Date: 10/05/2018
 * Time: 10:30
 */

namespace DepotBundle\Controller;


use DepotBundle\Entity\Commentaire;
use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe_Devoir;
use DepotBundle\Entity\Groupe_projet;
use DepotBundle\Entity\UserGroupeProjet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GroupeProjetController extends Controller
{
    public function newAction(Request $request, Devoir $devoir)
    {
        $flag = false;
        $groupes_projets = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["devoir" => $devoir]);
        foreach ($groupes_projets as $groupes_projet) {
            $users_groupes_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $groupes_projet]);
            foreach ($users_groupes_projets as $users_groupes_projet)
            {
                if($this->getUser()->getId() == $users_groupes_projet->getUser()->getId())
                {
                    $flag = true;
                }
            }
        }

        if($flag == true)
        {
            $this->addFlash("error", "L'utilisateur appartient déjà à un groupe");
        }else
        {
            $groupeProjet = new Groupe_projet();
            $groupeProjet->setDevoir($devoir);
            $groupeProjet->setName($this->getUser()->getLastName().$devoir->getId());

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
        $users_groupes_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet"=>$groupe_projet]);
        $user_groupe_projet = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findOneBy(["groupe_projet"=>$groupe_projet, "user"=>$this->getUser()]);
        $devoir = $groupe_projet->getDevoir();

        $em = $this->getDoctrine()->getManager();

        if($user_groupe_projet->getLeader() == true) {
            if (count($users_groupes_projets) != 1) {
                $this->addFlash("error", "Vous ne pouvez pas quitter le groupe, des personnes y sont présente.");
            } else {
                $em->remove($user_groupe_projet);
                $em->flush();

                $em->remove($groupe_projet);
                $em->flush();
            }
        }
        else{
            $em->remove($user_groupe_projet);
            $em->flush();
        }

        return $this->redirectToRoute("show_devoir", ["devoir" => $devoir->getId()]);
    }
}