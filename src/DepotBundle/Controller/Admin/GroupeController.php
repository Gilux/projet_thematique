<?php

namespace DepotBundle\Controller\Admin;

use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\UE;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver\SQLAnywhere\SQLAnywhereException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class GroupeController extends Controller
{
    public function indexAction()
    {

    }

    public function listAction()
    {
        $ues = $this->getDoctrine()->getRepository(UE::class)->findAll();

        return $this->render('DepotBundle:Admin\UE:list.html.twig', array('ues' => $ues ));
    }

    public function editAction(Request $request, Groupe $groupe)
    {
        $editForm = $this->createForm('DepotBundle\Form\Type\Admin\GroupeType', $groupe);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($groupe->getUsers() as $user) {
                $flag = false;
                foreach ($user->getGroupes() as $g) {
                    if ($groupe == $g) {
                        $flag = true;
                    }
                }
                if (!$flag) {
                    $user->addGroupe($groupe);
                    $em->persist($user);
                }
            }

            $users = $this->getDoctrine()->getRepository(User::class)->findByRole("ROLE_ETUDIANT");

            dump($users);

            //$em->flush();
            //return $this->redirectToRoute('ue_edit', ["id" => $groupe->getUE()->getId()]);
        }
        return $this->render('DepotBundle:Admin\Groupe:edit.html.twig', array(
            'groupe' => $groupe,
            'edit_form' => $editForm->createView(),
        ));
    }

    public function addAction(Request $request, UE $ue)
    {
        $groupe = new Groupe();
        $form = $this->createForm('DepotBundle\Form\Type\Admin\GroupeType', $groupe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupe->setUE($ue);

            $em = $this->getDoctrine()->getManager();

            foreach ($groupe->getUsers() as $user) {
                $user->addGroupe($groupe);
                $em->persist($user);
            }

            $em->persist($groupe);
            $em->flush();

            return $this->redirectToRoute('ue_edit', ["id" => $ue->getId()]);
        }

        return $this->render('DepotBundle:Admin\Groupe:add.html.twig', array(
            'groupe' => $groupe,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, UE $ue)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($ue);
        try{
            $em->flush();
            return $this->redirectToRoute('ue_list');
        }
        catch (\Doctrine\DBAL\DBALException $e)
        {
            $this->addFlash("error", "Impossible de supprimer cette UE car elle est liée à d'autres composants.");
            return $this->redirectToRoute('ue_list');
        }
    }
}