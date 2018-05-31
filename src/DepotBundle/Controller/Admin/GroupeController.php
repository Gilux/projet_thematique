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
use DepotBundle\Entity\UserGroupeProjet;
use DepotBundle\Entity\Groupe_projet;

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
        $us = $this->getDoctrine()->getRepository(User::class)->findByRole("ROLE_ETUDIANT");
        $users_in_groupe = $this->getDoctrine()->getRepository(User::class)->findByGroupe($groupe);
        
        $groupes = $this->getDoctrine()->getRepository(Groupe::class)->findBy(["UE"=>$groupe->getUE()]);
        
        $users = $us;
        
        $ar_unset = array();
        foreach($groupes as $grp)
        {
            $users_in_groupes = $this->getDoctrine()->getRepository(User::class)->findByGroupe($grp);
            foreach($users_in_groupes as $uig)
            {
                for($i=0;$i<count($users);$i++)
                {
                    if($users[$i] == $uig)
                    {
                        array_push($ar_unset,$i);                        
                    }
                }
            }
        }
        for($i=0;$i<count($ar_unset);$i++)
        {
            unset($users[$ar_unset[$i]]);
        }
            
        
        $editForm = $this->createForm('DepotBundle\Form\Type\Admin\GroupeType', $groupe);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ue_edit', ["id" => $groupe->getUE()->getId()]);
        }
        return $this->render('DepotBundle:Admin\Groupe:edit.html.twig', array(
            'users_in_groupe' => $users_in_groupe,
            'users' => $users,
            'groupe' => $groupe,
            'edit_form' => $editForm->createView(),
        ));
    }
    
    public function addToGroupeAction(User $user, Groupe $groupe)
    {
        $user->addGroupe($groupe);
        $this->getDoctrine()->getManager()->flush();
        
        return $this->redirectToRoute('groupe_edit', ["id" => $groupe->getId()]);
    }
    
    public function removeToGroupeAction(User $user, Groupe $groupe)
    {
        $user_groupe_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["user" => $user]);
        $flag = false;
        foreach ($user_groupe_projets as $ugp)
        {
            $gp = $this->getDoctrine()->getRepository(Groupe_projet::class)->find($ugp->getGroupeProjet());
            if($groupe == $gp->getGroupe())
                $flag = true;
        }
        if(!$flag)
        {
            $user->removeGroupe($groupe);
            $this->getDoctrine()->getManager()->flush();
        }
        else
        {
            $this->addFlash('error', "Impossible de retirer cet étudiant du groupe car il est lié un un groupe de projet d'un devoir.");
        }
        
        return $this->redirectToRoute('groupe_edit', ["id" => $groupe->getId()]);
    }

    public function addAction(Request $request, UE $ue)
    {
        $groupe = new Groupe();
        $form = $this->createForm('DepotBundle\Form\Type\Admin\GroupeType', $groupe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupe->setUE($ue);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();

            return $this->redirectToRoute('ue_edit', ["id" => $ue->getId()]);
        }

        return $this->render('DepotBundle:Admin\Groupe:add.html.twig', array(
            'groupe' => $groupe,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, Groupe $groupe)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($groupe);
        try{
            $em->flush();
            return $this->redirectToRoute('ue_edit', ["id"=>$groupe->getUE()->getId()]);
        }
        catch (\Doctrine\DBAL\DBALException $e)
        {
            $this->addFlash("error", "Impossible de supprimer ce groupe car il est liée à d'autres composants.");
            return $this->redirectToRoute('ue_edit', ["id"=>$groupe->getUE()->getId()]);
        }
    }
}