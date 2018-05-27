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
        $data = [];
        $user = $this->getUser();
        foreach ($user->getUes() as $ue) {
            foreach ($ue->getDevoirs() as $devoir) {
                $data[] = $devoir;
            }
        }
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
