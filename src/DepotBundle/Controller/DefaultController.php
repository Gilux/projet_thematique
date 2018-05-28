<?php

namespace DepotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')) {
            return $this->forward('DepotBundle:Default:getEtudiantDevoirs');
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            return $this->forward('DepotBundle:Default:getEnseignantDevoirs');
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function getEtudiantDevoirsAction()
    {
        $data = [];
        $user = $this->getUser();
        $groupes = $user->getGroupes();

        foreach ($groupes as $g) {
            $groupes_devoir = $g->getGroupeDevoir();
            foreach ($groupes_devoir as $gd) {
                $gpRepo = $this->getDoctrine()->getRepository("DepotBundle:Groupe_projet");
                $groupes_rendus = $gpRepo->findByDevoir($gd->getDevoir());
                $gp = $this->getDoctrine()->getRepository("DepotBundle:Groupe_projet")->findByDevoirAndUser($gd->getDevoir(), $user);

                $data[] = [
                    "id" => $gd->getDevoir()->getId(),
                    "ue" => $gd->getDevoir()->getUe()->getNom(),
                    "user" => $gd->getDevoir()->getUser(),
                    "titre" => $gd->getDevoir()->getTitre(),
                    "groupe" => $groupes_rendus,
                    "date_rendu" => $gd->getDateARendre(),
                    "date_rendu_file" => $gp ? $gp->getDate() : false,
                ];

            }
        }

        return $this->render('DepotBundle:Default:index.html.twig', array('data' => $data));
    }


    public function getEnseignantDevoirsAction()
    {

        $repo_groupe_projet = $this->getDoctrine()->getEntityManager()->getRepository("DepotBundle:Groupe_projet");
        $repo_groupe_devoir = $this->getDoctrine()->getEntityManager()->getRepository("DepotBundle:Groupe_devoir");
        $data = [];
        $user = $this->getUser();
        $nombre_rendus = 0;
        $groupes_devoir = 0;
        foreach ($user->getUes() as $ue) {
            foreach ($ue->getDevoirs() as $devoir) {
                //pour chaque devoir compter le nombre de groupe_projet
                $gps = $repo_groupe_projet->findByDevoir($devoir);
                foreach ($gps as $gp) {
                    if ($gp->getFichier() != null) {
                        $nombre_rendus++;
                    }
                    $groupes_devoir++;
                }
                //obtenir toutes les dates à rendre pour tout les groupe devoirs
                $groupes_devoirs = $repo_groupe_devoir->findByDevoir($devoir);
                foreach ($groupes_devoirs as $groupe_devoir) {
                    $dates_a_rendre[] = $groupe_devoir->getDateARendre();
                }
                if (count($dates_a_rendre) >= 2) {
                    $devoir->min_date_a_rendre = min($dates_a_rendre);
                    $devoir->max_date_a_rendre = max($dates_a_rendre);
                } else {
                    $devoir->unique_date_a_rendre = $dates_a_rendre[0];
                }
                $devoir->nombre_rendus = $nombre_rendus;
                $devoir->groupes_devoir = $groupes_devoir;

                $data[] = $devoir;
            }
        }

//        dump($data);
//        exit();
        return $this->render('DepotBundle:Default:index.html.twig', array('data' => $data));
    }

    public function getNotifications()
    {
        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());
        $manager = $this->get('mgilet.notification');
        $entity = $manager->getNotifiableEntity($user);
        $interface = $manager->getNotifiableInterface($entity);
        return $this->render(
            'notification/notifications.html.twig',
            array("notifiableInterface" => $interface, "notifiableNotifications" => $entity->getnotifiableNotifications(), "user" => $user)
        );
    }
}
